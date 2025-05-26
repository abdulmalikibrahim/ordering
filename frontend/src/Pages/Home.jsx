import React, { useEffect, useState } from 'react';
import Index from '../Layout/Index';
import SelectBoxPIC from '../Component/SelectBoxPIC';
import Form from 'react-bootstrap/Form';
import Graph from '../Component/Graph';

const Home = ({API_URL}) => {
    return (
        <Index API_URL={API_URL}>
            <RenderGraph API_URL={API_URL}/>
        </Index>
    );
}

const RenderGraph = ({API_URL}) => {
    const [periodeMonth, setPeriodeMonth] = useState(new Date().getMonth());
    const [periodeYear, setPeriodeYear] = useState(new Date().getFullYear());
    const [pic, setpic] = useState('all');
    return (
        <>
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
                <div className="col-lg-12 p-4">
                    <div className="card mb-4">
                        <div className="card-header text-center p-2">
                            <h4 className='m-0'>SPECIAL ORDER</h4>
                        </div>
                    </div>
                    <Graph API_URL={API_URL} month={periodeMonth} year={periodeYear} pic={pic} type={"upload_so"} />
                </div>
                <div className="col-lg-12 p-4">
                    <div className="card mb-4">
                        <div className="card-header text-center p-2">
                            <h4 className='m-0'>ADDITIONAL ORDER</h4>
                        </div>
                    </div>
                    <Graph API_URL={API_URL} month={periodeMonth} year={periodeYear} pic={pic} type={"additional"} />
                </div> 
                <div className="col-lg-12 p-4">
                    <div className="card mb-4">
                        <div className="card-header text-center p-2">
                            <h4 className='m-0'>REDUCE ORDER</h4>
                        </div>
                    </div>
                    <Graph API_URL={API_URL} month={periodeMonth} year={periodeYear} pic={pic} type={"reduce"} />
                </div> 
            </div>
        </>
    )
}

export default Home;