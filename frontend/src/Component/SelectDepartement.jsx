import axios from 'axios';
import { useEffect, useState } from 'react';

const SelectDepartement = ({deptName, API_URL}) => {
    const [dept, setDept] = useState(deptName);
    const [dataDept, setdataDept] = useState([]);

    // Ambil daftar departemen
    const GetDept = async () => {
        try {
            const response = await axios.get(`${API_URL}/get_dept`);
            if (response.status === 200) {
                setdataDept(response.data);
            }
        } catch (error) {
            console.error(error);
        }
    };

    useEffect(() => {
        GetDept();

    //eslint-disable-next-line
    }, [])
    
    return (
        <select className="mb-2 form-control text-dark" value={dept} onChange={(e) => setDept(e.target.value)}>
            <option value="">Pilih Departement</option>
            {dataDept.map((value) => (
                <option key={value.id} value={value.id}>{value.name}</option>
            ))}
        </select>
    );
}

export default SelectDepartement;