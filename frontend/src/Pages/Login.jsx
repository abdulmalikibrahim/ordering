import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import Graph from '../Component/Graph';

const Login = ({API_URL}) => {
    const [username,setUsername] = useState('')
    const [password,setPassword] = useState('')
    const [loadingLabel,setLoadingLabel] = useState('SIGN IN')
    const [errorMessage,setErrorMessage] = useState('');
    const navigate = useNavigate();
    const yearNow = new Date().getFullYear();
    const monthNow = new Date().getMonth();

    const handleSubmit = async () => {
        try {
            setLoadingLabel('Memeriksa Akun...')
            const formdata = new FormData()
            formdata.append('username',username)
            formdata.append('password',password)
            const response = await axios.post(`${API_URL}/login`,formdata, {
                withCredentials: true
            })
            if(response.status === 200){
                navigate('/home')
            }
        } catch (error) {
            setLoadingLabel('SIGN IN')
            console.error(error)
            if(error.response.data){
                setErrorMessage(error.response.data.res)
            }
        }
    }

    const checkLogin = async () => {
        try {
            setLoadingLabel('Checking Your Session...')
            const response = await axios.post(`${API_URL}/checkLogin`, {}, {
                withCredentials: true
            })
            if(response.status === 200){
                navigate('/home')
            }
            if(response.status === 202){
                navigate('/')
            }
            setLoadingLabel('SIGN IN')
        } catch (error) {
            setLoadingLabel('SIGN IN')
            console.error(error)
            if(error.response){
                setErrorMessage(error.response.data.res)
            }
        }
    }

    useEffect(() => {
        checkLogin()
        //eslint-disable-next-line
    },[])
    return(
        <div className="container-scroller" 
            style={{
                height: '100vh',
                backgroundImage: `url('https://img.freepik.com/free-vector/realistic-mountain-landscape-illustration_23-2149156109.jpg?semt=ais_hybrid&w=740')`,
                backgroundSize: 'cover',
                backgroundPosition: 'center'
            }}>
            <div className="row" style={{height: '100vh'}}>
                <div className="col-9">
                    <div className='mt-4 p-3' style={{overflowY: 'scroll', height: '100vh'}}>
                        <div className="row">
                            <div className="col-12 mb-2">
                                <div className="card mb-4">
                                    <div className="card-header text-center p-3 bg-info text-light"><h3 className='mb-0'>SPECIAL ORDER</h3></div>
                                    <div className="card-body p-0 pb-5 pt-4">
                                        <Graph API_URL={API_URL} month={monthNow} year={yearNow} pic={'all'} type={'upload_so'} />
                                    </div>
                                </div>
                            </div>
                            <div className="col-12 mb-2">
                                <div className="card mb-4">
                                    <div className="card-header text-center p-3 bg-info text-light"><h3 className='mb-0'>ADDITIONAL ORDER</h3></div>
                                    <div className="card-body p-0 pb-5 pt-4">
                                        <Graph API_URL={API_URL} month={monthNow} year={yearNow} pic={'all'} type={'additional'} />
                                    </div>
                                </div>
                            </div>
                            <div className="col-12">
                                <div className="card mb-4">
                                    <div className="card-header text-center p-3 bg-info text-light"><h3 className='mb-0'>REDUCE ORDER</h3></div>
                                    <div className="card-body p-0 pb-5 pt-4">
                                        <Graph API_URL={API_URL} month={monthNow} year={yearNow} pic={'all'} type={'reduce'} />
                                    </div>
                                </div>
                            </div>
                            <div className="col-lg-12 text-center mb-3 text-light fw-bold" style={{fontSize:"9pt"}}>Created By System Improvement PCD (Abdul Malik Ibrahim)</div>
                        </div>
                    </div>
                </div>
                <div className="p-0 col-lg-3 d-flex align-items-center justify-content-center">
                    <div className="card text-left p-5">
                        <div className="text-center">
                            <h1 style={{fontSize:"2.5rem", fontFamily:"calibri"}} className='mt-3 mb-4 fw-bold'>ORDERING APPS</h1>
                        </div>
                        <div className="brand-logo">
                            <h3>LOGIN</h3>
                        </div>
                        <h6 className="font-weight-light">Sign in to continue.</h6>
                        {
                            errorMessage && 
                            <div className="card">
                                <div className="card-body bg-danger text-light p-2">{ errorMessage }</div>
                            </div>
                        }
                        <form className="pt-3">
                            <div className="form-group">
                                <input type="text" className="form-control form-control-lg" placeholder="Username" value={username} onChange={(e) => setUsername(e.target.value)} />
                            </div>
                            <div className="form-group">
                                <input type="password" className="form-control form-control-lg" placeholder="Password" value={password} onChange={(e) => setPassword(e.target.value)} />
                            </div>
                            <div className="mt-3 d-grid gap-2">
                                <a className="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn" onClick={() => handleSubmit()}>{ loadingLabel }</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Login;