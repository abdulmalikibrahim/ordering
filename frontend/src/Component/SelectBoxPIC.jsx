import axios from 'axios';
import React, { useEffect, useState } from 'react';
import Form from 'react-bootstrap/Form';

const SelectBoxPIC = ({setpic,pic,API_URL,optionAll}) => {
    const [picOptions, setPICOptions] = useState([]);
    const getPICShop = async () => {
        try {
            const response = await axios.get(`${API_URL}/get_pic_shop`);
            const fetchedData = Array.isArray(response.data) ? response.data : [];
            setPICOptions(fetchedData);
        } catch (error) {
            console.error("Error : ", error);
        }
    }

    useEffect(() => {
        getPICShop();
        //eslint-disable-next-line
    }, []);

    return(
        <Form.Control
            as="select"
            value={pic}
            onChange={(e) => setpic(e.target.value)}
            className="mb-2 text-dark"
        >
            <option value="">Select PIC</option>
            {optionAll && <option value="all">All</option>}
            {picOptions.map((pic, i) => (
                <option key={i} value={pic.id}>{`${pic.name} (${pic.name_dept})`}</option>
            ))}
        </Form.Control>
    )
}

export default SelectBoxPIC;