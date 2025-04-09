import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const Login = ({API_URL}) => {
    const [username,setUsername] = useState('')
    const [password,setPassword] = useState('')
    const [loadingLabel,setLoadingLabel] = useState('SIGN IN')
    const [errorMessage,setErrorMessage] = useState('');
    const navigate = useNavigate()

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
        <div className="container-scroller">
            <div className="container-fluid page-body-wrapper full-page-wrapper">
                <div className="content-wrapper d-flex align-items-center auth">
                    <div className="row flex-grow">
                        <div className="col-lg-4 mx-auto">
                            <div className="auth-form-light text-left p-5">
                                <div className="brand-logo">
                                    <h3>ORDERING APPS</h3>
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
            </div>
        </div>
    )
}

export default Login;