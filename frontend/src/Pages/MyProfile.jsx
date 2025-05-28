import React, { useEffect, useState } from 'react';
import { useGlobal } from '../GlobalContext';
import Index from '../Layout/Index';
import SelectDepartement from '../Component/SelectDepartement';
import Button from 'react-bootstrap/esm/Button';
import axios from 'axios';
import Swal from 'sweetalert2';

const MyProfile = () => {
    const {
        levelAccount,
        setLevelAccount,
        nameAccount,
        setNameAccount,
        usernameAccount,
        setUsernameAccount,
        deptIdAccount,
        setDeptIdAccount,
        emailAccount,
        setEmailAccount,
        API_URL

    } = useGlobal();
    const [ level, setLevel ] = useState(levelAccount);
    const [ name, setName ] = useState(nameAccount);
    const [ username, setUsername ] = useState(usernameAccount);
    const [ deptId, setDeptId ] = useState(deptIdAccount);
    const [ email, setEmail ] = useState(emailAccount);
    const [ password, setPassword ] = useState('');
    const [ passwordConfirm, setPasswordConfirm ] = useState('');
    console.log(usernameAccount);

    useEffect(() => {
        setLevel(levelAccount);
        setName(nameAccount);
        setUsername(usernameAccount);
        setDeptId(deptIdAccount);
        setEmail(emailAccount);

        //eslint-disable-next-line
    }, [])

    const prosesUpdate = async () => {
        try {
            const formdata = new FormData();
            formdata.append('level',level);
            formdata.append('name',name);
            formdata.append('username',username);
            formdata.append('dept',deptId);
            formdata.append('email',email);
    
            const response = await axios.post(`${API_URL}/update_profile`, formdata, {
                withCredentials: true
            });
            if(response.status == 200){
                Swal.fire('Sukses','Pofile berhasil dirubah','success');
                setLevelAccount(level);
                setNameAccount(name);
                setUsernameAccount(username);
                setDeptIdAccount(deptId);
                setEmailAccount(email);
            }
        } catch (error) {
            if(error.response.data){
                Swal.fire('Gagal',error.response.data.res,'error');
            }else{
                Swal.fire('Error','Maaf ada kesalahan tak terduga','error');
            }
            console.error(error);
        }
    }

    const gantiPassword = async () => {
        try {
            const formdata = new FormData();
            formdata.append('password',password);
            formdata.append('passwordconfirm',passwordConfirm);
            const response = await axios.post(`${API_URL}/change_password`,formdata,{
                withCredentials: true
            });
            if(response.status == 200){
                Swal.fire('Sukses','Password berhasil di ganti','success');
                setPassword('');
                setPasswordConfirm('');
            }
        } catch (error) {
            if(error.response.data){
                Swal.fire("Gagal",error.response.data.res,"error");
            }else{
                Swal.fire("Error","Maaf ada kesalahan tak terduga","error");
            }
            console.error(error);
        }
    }

    return(
        <Index API_URL={API_URL}>
            <div className="row">
                <div className="col-lg-4">
                    <div className="card">
                        <div className="card-header p-3"><h5 className='m-0'>Form Update Profile</h5></div>
                        <div className="card-body">
                            <p className='mb-2'>Nama</p>
                            <input type="text" className="form-control mb-2" placeholder='Nama' value={name} onChange={(e) => setName(e.target.value)} />
                            <p className='mb-2'>Username</p>
                            <input type="text" className="form-control mb-2" placeholder='Username' value={username} onChange={(e) => setUsername(e.target.value)} />
                            <p className='mb-2'>Level</p>
                            <select className="form-control mb-2 text-dark" value={levelAccount} onChange={(e) => setLevel(e.target.value)}>
                                <option value="2">User</option>
                                <option value="3">Supervisor</option>
                                <option value="4">Manager</option>
                                {
                                    level === "1" && <option value="1">Admin</option>
                                }
                            </select>
                            <p className='mb-2'>Departement</p>
                            <SelectDepartement deptName={deptId} API_URL={API_URL} setDept={setDeptId} />
                            <p className='mb-2'>Email</p>
                            <input type="email" className="form-control mb-2" placeholder='Email' value={email} onChange={(e) => setEmail(e.target.value)} />
                        </div>
                        <div className="card-footer text-end">
                            <Button variant='info' onClick={prosesUpdate}>Simpan</Button>
                        </div>
                    </div>
                </div>
                <div className="col-lg-4">
                    <div className="card">
                        <div className="card-header p-3"><h5 className='m-0'>Form Ganti Password</h5></div>
                        <div className="card-body">
                            <p className='mb-2'>Masukkan Password Baru</p>
                            <input type="password" className="form-control mb-2" placeholder='Masukkan password baru' value={password} onChange={(e) => setPassword(e.target.value)} />
                            <p className='mb-2'>Ulangi Password Baru</p>
                            <input type="password" className="form-control mb-2" placeholder='Ulangi password baru' value={passwordConfirm} onChange={(e) => setPasswordConfirm(e.target.value)} />
                            <p className="text-danger m-0 mt-3" style={{fontSize:'10pt'}}>Note : Jika lupa password dan ingin reset bisa hubungi PCD Ordering</p>
                        </div>
                        <div className="card-footer text-end">
                            <Button variant='primary' className='ms-2' onClick={gantiPassword}>Ganti Password</Button>
                        </div>
                    </div>
                </div>
            </div>
        </Index>
    )
}

export default MyProfile;