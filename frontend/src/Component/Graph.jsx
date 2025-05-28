import React, { useState, useEffect } from 'react';
import { 
    PieChart, Pie, Cell, 
    ComposedChart, Bar, Line, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer 
} from 'recharts';
import axios from 'axios';

const Graph = ({ API_URL,month,year,pic,type }) => {
    const [data, setData] = useState([]);
    const [dataPrice, setDataPrice] = useState([]);
    const [data_pie, setDataPie] = useState([]);
    const [data_status, setDataStatus] = useState([]);
    
    const getData = async () => {
        try {
            const formdata = new FormData();
            formdata.append('month', month);
            formdata.append('year', year);
            formdata.append('pic', pic);
            formdata.append('type', type);
            const response = await axios.post(`${API_URL}/get_data_graph`, formdata, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            if(response.status === 200) {
                setData(response.data.res.bar);
                setDataPie(response.data.res.pie);
                setDataPrice(response.data.res.price);
                setDataStatus(response.data.res.status);
            }
        } catch (error) {
            console.error("Error : ", error);
        }
    }

    useEffect(() => {
        getData();
    }
    , [month, year, pic]);

    // Gabungkan bar dan price ke dalam satu array
    const mergedData = data.map((item, index) => ({
        name: item.name,
        Qty: item.value,
        Price: dataPrice[index]?.value || 0
    }));

    return (
    <div className="row" style={{ position: "relative", height: 300 }}>
        <div className="col-lg-6">
            {/* Judul di luar container yang punya height */}
            <h5 style={{ textAlign: 'center', marginBottom: '10px', fontWeight: 'bold' }}>
                Jumlah Transaksi & Harga
            </h5>

            {/* Wrapper chart */}
            <div style={{ position: "relative", height: 300 }}>
                <ResponsiveContainer width="100%" height="100%">
                    <ComposedChart data={mergedData}>
                        <XAxis 
                            dataKey="name"
                            tick={{
                                fontSize: 10,
                                fill: '#8884d8',
                                fontFamily: 'Arial'
                            }}
                        />
                        <YAxis
                            yAxisId="left"
                            domain={[0, 'dataMax + 5']}
                            tick={{
                                fontSize: 10, // ukuran font
                                fill: '#8884d8',
                                fontFamily: 'Arial'
                            }}
                        />
                        <YAxis
                            yAxisId="right"
                            orientation="right"
                            domain={[0, 'dataMax + 100000']}
                            tick={{
                                fontSize: 10,
                                fill: '#82ca9d',
                                fontFamily: 'Arial'
                            }}
                            tickFormatter={(value) => `${(value / 1000)}k`}
                        />
                        <Tooltip formatter={(value, name) => {
                            return name === 'Price'
                                ? [`Rp ${value.toLocaleString()}`, 'Price']
                                : [value, 'Qty'];
                        }} />
                        <Legend />

                        <Bar 
                            yAxisId="left" 
                            dataKey="Qty" 
                            fill="#0088FE" 
                            label={{ position: 'top' }} 
                            minPointSize={5}
                        >
                            {mergedData.map((entry, index) => (
                                <Cell key={`cell-bar-${index}`} fill="#0088FE" />
                            ))}
                        </Bar>

                        <Line 
                            yAxisId="right" 
                            type="monotone" 
                            dataKey="Price" 
                            stroke="#82ca9d" 
                            strokeWidth={2} 
                            dot={{ r: 2 }} 
                        />
                    </ComposedChart>
                </ResponsiveContainer>
            </div>
        </div>
        <div className="col-lg-3">
            {/* Judul di luar container yang punya height */}
            <h5 style={{ textAlign: 'center', marginBottom: '10px', fontWeight: 'bold' }}>
                Progress Approval
            </h5>

            {/* Wrapper chart */}
            <div style={{ position: "relative", height: 300 }}>
                <ResponsiveContainer width="100%" height="100%">
                    <ComposedChart data={data_status}>
                        <XAxis 
                            dataKey="name"
                            tick={{
                                fontSize: 10,
                                fill: '#8884d8',
                                fontFamily: 'Arial'
                            }}
                        />
                        <YAxis
                            yAxisId="left"
                            domain={[0, 'dataMax + 5']}
                            tick={{
                                fontSize: 10, // ukuran font
                                fill: '#8884d8',
                                fontFamily: 'Arial'
                            }}
                        />
                        <Tooltip formatter={(value, name) => {
                            return name === 'Price'
                                ? [`Rp ${value.toLocaleString()}`, 'Price']
                                : [value, 'Qty'];
                        }} />
                        <Legend />

                        <Bar 
                            yAxisId="left" 
                            dataKey="Qty" 
                            fill="#0088FE" 
                            label={{ position: 'top' }} 
                            minPointSize={5}
                        >
                            {mergedData.map((entry, index) => (
                                <Cell key={`cell-bar-${index}`} fill="#0088FE" />
                            ))}
                        </Bar>
                    </ComposedChart>
                </ResponsiveContainer>
            </div>
        </div>
        <div className="col-lg-3" >
            {/* Judul di luar container yang punya height */}
            <h5 style={{ marginBottom: '10px', marginLeft: '4rem', fontWeight: 'bold' }}>
                Status SO
            </h5>

            {/* Wrapper chart */}
            <div style={{ position: "relative", height: 300 }}>
                <PieChart width={250} height={300}>
                    <Pie
                        data={data_pie}
                        cx="50%"
                        cy="50%"
                        labelLine={false}
                        outerRadius={100}
                        fill="#8884d8"
                        dataKey="value"
                        label={renderCustomizedLabel}
                    >
                        {data_pie.map((entry, index) => (
                            <Cell key={`cell-${index}`} fill={entry.fill} />
                        ))}
                    </Pie>
                    <Legend />
                </PieChart>
            </div>
        </div>
    </div>
    );
};

const renderCustomizedLabel = ({
    cx, cy, midAngle, innerRadius, outerRadius, percent, name, value, index
  }) => {
    const RADIAN = Math.PI / 180;
    const radius = innerRadius + (outerRadius - innerRadius) * 0.6; // ini seperti "offset"
    const x = cx + radius * Math.cos(-midAngle * RADIAN);
    const y = cy + radius * Math.sin(-midAngle * RADIAN);
  
    return (
      <text x={x} y={y} fill="white" textAnchor="middle" dominantBaseline="central" fontSize={12}>
        {`${value} (${(percent * 100).toFixed(0)})`}%
      </text>
    );
};

export default Graph;