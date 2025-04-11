import React from 'react';
import Index from '../Layout/Index';

const Home = ({API_URL}) => {
    return (
        <Index API_URL={API_URL}>
            {/* <div className="row">
                <div className="col-3">
                    <Card type={'success'} title={'TOTAL'} count={15} periode={'Maret 2025'} icon={'fas fa-table'} />
                </div>
                <div className="col-3">
                    <Card type={'danger'} title={'REMAIN'} count={15} periode={'Maret 2025'} icon={'fas fa-clock-rotate-left'} />
                </div>
                <div className="col-3">
                    <Card type={'info'} title={'FINISH'} count={15} periode={'Maret 2025'} icon={'fas fa-circle-check'} />
                </div>
                <div className="col-3">
                    <Card type={'warning'} title={'UNDER PROGRESS'} count={15} periode={'Maret 2025'} icon={'fas fa-bars-progress'} />
                </div>
            </div> */}
            <h2>DASHBOARD UNDER DEVELOPMENT</h2>
        </Index>
    );
}

const Card = ({type,title,count,periode,icon}) => {
    const typeCard = `card text-white bg-${type} border-0`;
    return(
        <div className={typeCard}>
            <div className="card-header"><h4 className='mb-0'><i className={`${icon} pe-2`}></i>{title}</h4></div>
            <div className="card-body p-3 d-flex justify-content-between align-items-start">
                <div>
                    <div className="fs-4 fw-semibold">{count} DATA</div>
                    <div>{periode}</div>
                </div>
            </div>
        </div>
    )
}

export default Home;