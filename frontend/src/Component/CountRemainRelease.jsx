import React, { useEffect, useState } from 'react';
import { useGlobal } from '../GlobalContext';
import axios from 'axios';
import Swal from 'sweetalert2';

const CountRemainRelease = () => {
    const {API_URL, reloadCountRemain} = useGlobal();
    const [countRemainRelease, setCountRemainRelease] = useState(0);
    const getCountSORemain = async () => {
        try {
            const response = await axios.get(`${API_URL}/count_remain_release`, {
                withCredentials: true
            })
            console.log(response)
            if(response?.status === 200){
                setCountRemainRelease(response.data.res)
            }
        } catch (error) {
            if(error.response.status === 400){
                Swal.fire('Error',error.response.data.res,'warning');
            }
            console.error(error)
        }
    }
    
    useEffect(() => {
        getCountSORemain();
        //eslint-disable-next-line
    }, [reloadCountRemain, window.location.pathname])

    return(
        <>
            {countRemainRelease > 0 ? <span className='badge badge-danger ms-2'>{countRemainRelease}</span> : ''}
        </>
    )
}

export default CountRemainRelease;