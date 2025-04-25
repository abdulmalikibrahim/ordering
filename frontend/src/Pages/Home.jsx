import React, { useEffect, useState } from 'react';
import Index from '../Layout/Index';
import SelectBoxPIC from '../Component/SelectBoxPIC';
import Form from 'react-bootstrap/Form';
import { PieChart, Pie, Cell, BarChart, Bar, XAxis, YAxis, ResponsiveContainer, Legend } from 'recharts';
import axios from 'axios';

const Home = ({API_URL}) => {
    const [periodeMonth, setPeriodeMonth] = useState(new Date().getMonth());
    const [periodeYear, setPeriodeYear] = useState(new Date().getFullYear());
    const [pic, setpic] = useState('all');
    return (
        <Index API_URL={API_URL}>
            <div className="row">
                <div className="col-lg-8">
                    <h2>SUMMARY IREGULER ORDER ADM KAP</h2>
                </div>
                <div className="col-lg-4">
                    <div className="row">
                        <div className="col-lg-6">
                            <SelectBoxPIC setpic={setpic} pic={pic} API_URL={API_URL} optionAll={true} />
                        </div>
                        <div className="col-lg-6">
                            <div className="input-group">
                                <Form.Control
                                    as="select"
                                    value={periodeMonth}
                                    onChange={(e) => setPeriodeMonth(e.target.value)}
                                    className="mb-2 text-dark"
                                >
                                    {
                                        Array.from({ length: 12 }, (_, i) => {
                                            const month = new Date(0, i).toLocaleString('default', { month: 'long' });
                                            return (
                                                <option key={i} value={i}>
                                                    {month}
                                                </option>
                                            );
                                        })
                                    }
                                </Form.Control>
                                <Form.Control
                                    as="select"
                                    value={periodeYear}
                                    onChange={(e) => setPeriodeYear(e.target.value)}
                                    className="mb-2 text-dark"
                                >
                                    {
                                        Array.from({ length: 5 }, (_, i) => {
                                            const year = new Date().getFullYear() - i;
                                            return (
                                                <option key={i} value={year}>
                                                    {year}
                                                </option>
                                            );
                                        })
                                    }
                                </Form.Control>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className="row">
                <div className="col-lg-6 p-4">
                    <div className="card mb-4">
                        <div className="card-header text-center p-2">
                            <h4 className='m-0'>SPECIAL ORDER</h4>
                        </div>
                    </div>
                    <Graph API_URL={API_URL} month={periodeMonth} year={periodeYear} pic={pic} type={"upload_so"} />
                </div>
                <div className="col-lg-6 p-4">
                    <div className="card mb-4">
                        <div className="card-header text-center p-2">
                            <h4 className='m-0'>ADDITIONAL ORDER</h4>
                        </div>
                    </div>
                    <Graph API_URL={API_URL} month={periodeMonth} year={periodeYear} pic={pic} type={"additional"} />
                </div> 
                <div className="col-lg-6 p-4">
                    <div className="card mb-4">
                        <div className="card-header text-center p-2">
                            <h4 className='m-0'>REDUCE ORDER</h4>
                        </div>
                    </div>
                    <Graph API_URL={API_URL} month={periodeMonth} year={periodeYear} pic={pic} type={"reduce"} />
                </div> 
            </div> 
        </Index>
    );
}

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
  

const Graph = ({ API_URL,month,year,pic,type }) => {
    const [data, setData] = useState([]);
    const [data_pie, setDataPie] = useState([]);
    
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
                console.log(response.data.res.bar);
                setData(response.data.res.bar);
                setDataPie(response.data.res.pie);
            }
        } catch (error) {
            console.error("Error : ", error);
        }
    }

    useEffect(() => {
        console.log("get data graph")
        getData();
    }
    , [month, year, pic]);

    return (
    <div className="row" style={{ position: "relative", height: 350 }}>
        <div className="col-lg-9 pe-4">
            <ResponsiveContainer width="100%" height="100%">
                <BarChart data={data}>
                    <Bar dataKey="value" fill="#8884d8" label={{ position: 'top' }} minPointSize={5}>
                        {data.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={"#0088FE"} />
                        ))}
                    </Bar>
                    <XAxis 
                        dataKey="name" 
                        tick={{ 
                            fontSize: 10, 
                            fill: '#8884d8', 
                            fontFamily: 'Arial' 
                        }}
                    />
                    <YAxis domain={[0, 'dataMax + 50']} hide={true} />
                </BarChart>
            </ResponsiveContainer>
        </div>
        <div className="col-lg-3" style={{ position: "absolute", top: 10, right: 50 }}>
            <PieChart width={300} height={300}>
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
    );
};

export default Home;