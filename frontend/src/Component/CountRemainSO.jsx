import React, { useEffect, useState } from 'react';
import { useGlobal } from '../GlobalContext';
import axios from 'axios';
import Swal from 'sweetalert2';

const CountRemainSO = () => {
    const {API_URL, reloadCountRemain} = useGlobal();
    const [countRemainSO, setCountRemainSO] = useState(0);
    const getCountSORemain = async () => {
        try {
            const response = await axios.get(`${API_URL}/count_remain_approve`, {
                withCredentials: true
            })
            if(response?.status === 200){
                setCountRemainSO(response.data.res)
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
            {countRemainSO > 0 ? <span className='badge badge-danger ms-2'>{countRemainSO}</span> : ''}
        </>
    )
}

export default CountRemainSO;