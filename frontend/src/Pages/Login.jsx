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
        <div className="container-scroller" style={{height: '100vh'}}>
            <div className="row" style={{height: '100vh'}}>
                <div className="col-8 position-relative">
                    <img src="https://logowik.com/content/uploads/images/511_daihatsu.jpg" style={{width:"150px", position: 'absolute', top: '10px', left: '10px'}} />
                    <center><h1 className='mt-5'>ORDERING APPS</h1></center>
                    <div className='mt-5 p-3' style={{overflowY: 'scroll', height: '86vh'}}>
                        <div className="row">
                            <div className="col-12 mb-5">
                                <h4>SPECIAL ORDER</h4>
                                <Graph API_URL={API_URL} month={monthNow} year={yearNow} pic={'all'} type={'upload_so'} />
                            </div>
                            <div className="col-12 mb-5">
                                <h4>ADDITIONAL ORDER</h4>
                                <Graph API_URL={API_URL} month={monthNow} year={yearNow} pic={'all'} type={'additional'} />
                            </div>
                            <div className="col-12">
                                <h4>REDUCE ORDER</h4>
                                <Graph API_URL={API_URL} month={monthNow} year={yearNow} pic={'all'} type={'reduce'} />
                            </div>
                        </div>
                    </div>
                </div>
                <div className="p-0 col-lg-4 d-flex align-items-center justify-content-center" 
                    style={{
                        backgroundImage: `url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSi3ouYfxjGUo6DneSeAIcPREd449GpWycsVZ1i6eXCUZr1fjeGQe9_mSNA2rkqYVA4hRI&usqp=CAU')`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center'
                    }}
                >
                    <div className="card text-left p-5">
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