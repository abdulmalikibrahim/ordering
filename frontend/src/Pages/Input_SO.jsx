import React, { useEffect, useState } from 'react';
import axios from 'axios';
import Index from '../Layout/Index';
import Button from "react-bootstrap/Button";
import Swal from 'sweetalert2';
import DataTable from 'react-data-table-component';
import Modal from 'react-bootstrap/Modal';
import Form from 'react-bootstrap/Form';

const Input_SO = ({API_URL}) => {
    const [reloadTable,setreloadTable] = useState(1);
    const [show, setShow] = useState(false);
    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);
    const [titleTable, settitleTable] = useState("");
    const [modeInput, setmodeInput] = useState("");

    useEffect(() => {
        const path = window.location.pathname;
        const pageName = path.split("/")[1];
        if (pageName === 'input_so') {
            settitleTable('Upload SO');
            setmodeInput('upload_so');
        } else if (pageName === 'reduce_order') {
            settitleTable('Reduce Order');
            setmodeInput('reduce');
        } else if (pageName === 'additional_order') {
            settitleTable('Additional Order');
            setmodeInput('additional');
        } 
    },[window.location.pathname])

    return(
        <Index API_URL={API_URL}>
            <div className="row">
                <div className="col-12 text-end mb-2">
                    <Button variant="outline-primary" onClick={() => handleShow()}><i className="fas fa-plus"></i> Tambah</Button>
                </div>
                <div className="col-12">
                    <Table API_URL={API_URL} reloadTable={reloadTable} setreloadTable={setreloadTable} titleTable={titleTable} />
                </div>
            </div>
            <FormAdd handleClose={handleClose} show={show} API_URL={API_URL} setreloadTable={setreloadTable} modeInput={modeInput} />
        </Index>
    )
}

const Table = ({API_URL, reloadTable, setreloadTable, titleTable}) => {
    const [data, setData] = useState([]);
    const [filteredData, setFilteredData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
  
    useEffect(() => {
        fetchData();
    }, [reloadTable]);
  
    const fetchData = async () => {
        setLoading(true);
        try {
            const response = await axios.get(`${API_URL}/get_data_so`);
            const fetchedData = Array.isArray(response.data) ? response.data : [];
            setData(fetchedData);
            setFilteredData(fetchedData);
        } catch (error) {
            console.error("Error fetching data:", error);
        }
        setLoading(false); // Tidak perlu di `finally`
    };

    // Update data ketika search berubah
    useEffect(() => {
        if (!search) {
            setFilteredData(data);
            return;
        }
    }, [search, data]);

    const ButtonAction = ({...props}) => {
        
        const ClickDelete = (id) => {
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data akan dihapus dan tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const formdata = new FormData();
                        formdata.append("id",id);
                        const response = await axios.post(`${API_URL}/delete_departement`, formdata);
                        if(response.status === 200){
                            Swal.fire("Berhasil!", "Data telah dihapus.", "success");
                            setreloadTable(Math.random() * 10);
                        }
                    } catch (error) {
                        Swal.fire("Gagal!", "Terjadi kesalahan saat menghapus.", "error");
                        console.error(error);
                        
                    }
                }
            });
        };

        return(
            <>
                <Button variant="danger" className="me-2 btn-sm" title="Hapus" onClick={() => ClickDelete(props.data.id)}><i className="fas fa-trash-alt"></i></Button>
            </>
        )
    }

    const columns = [
        { name: "No", selector: (row, index) => index + 1, sortable: true, width: "70px" },
        { name: "Created", selector: (row) => row.created_time, sortable: true },
        { name: "SO Number", selector: (row) => row.so_number, sortable: true },
        { name: "Creator", selector: (row) => row.creator, sortable: true },
        { name: "Dept Creator", selector: (row) => row.dept_creator, sortable: true },
        { name: "Shop Code", selector: (row) => row.shop_code, sortable: true },
        { name: "Total Part", selector: (row) => row.total_part, sortable: true },
        { name: "SPV Sign", selector: (row) => row.spv_sign+"<br>"+row.spv_sign_time, sortable: true },
        { name: "MNG Sign", selector: (row) =>  row.mng_sign+"<br>"+row.mng_sign_time, sortable: true },
        { name: "Action", selector: (row) => 
            <ButtonAction data={row} />, 
        sortable: true },
    ];
  
    return (
        <div className="mt-3">
            <div className="row">
                <div className="col-9">
                    <h2 className="mb-3">{titleTable}</h2>
                </div>
                <div className="col-3 text-end">
                    <input
                        type="text"
                        placeholder="Search..."
                        className="form-control mb-2"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                </div>
            </div>
            
            <DataTable
                columns={columns}
                data={filteredData}
                progressPending={loading}
                pagination
                highlightOnHover
            />
        </div>
    );
}

const FormAdd = ({ handleClose, show, API_URL, setreloadTable, modeInput }) => {
    const [inputMode, setInputMode] = useState("single");
    const [parts, setParts] = useState([{ partNumber: "", qty: "", shopCode: "" }]);
    const [file, setFile] = useState(null);
  
    const shopOptions = ["Assy 3", "Assy 4", "Weld 3", "Weld 4"];
  
    const addPart = () => {
        setParts([...parts, { partNumber: "", qty: "", shopCode: "" }]);
    };
  
    const handleChange = (index, field, value) => {
        const newParts = [...parts];
        newParts[index][field] = value;
        setParts(newParts);
    };
  
    const handleFileUpload = (event) => {
        setFile(event.target.files[0]);
    };
  
    const saveData = async () => {
        try {
            const formData = new FormData();
            formData.append("modeInput",modeInput);
            if (inputMode === "single") {
                formData.append("parts",parts);
                await axios.post(`${API_URL}/save_part`, formData);
            } else {
                formData.append("file", file);
                await axios.post(`${API_URL}/upload_parts`, formData);
            }
            setreloadTable((prev) => prev + 1);
            handleClose();
        } catch (error) {
            console.error("Error saving data:", error);
        }
    };
  
    return (
    <Modal show={show} onHide={handleClose}>
        <Modal.Header closeButton>
          <Modal.Title>Form Add Part</Modal.Title>
        </Modal.Header>
        <Modal.Body>
            <Form>
                <Form.Group>
                    <Form.Label>Input Mode</Form.Label>
                    <Form.Control
                        as="select"
                        value={inputMode}
                        onChange={(e) => setInputMode(e.target.value)}
                        className='text-dark'
                    >
                        <option value="single">Input Single</option>
                        <option value="upload">Upload File</option>
                    </Form.Control>
                </Form.Group>
                <hr />
    
                {inputMode === "single" ? (
                <>
                    {parts.map((part, index) => (
                    <div key={index} className="mb-2">
                        <Form.Control
                            type="text"
                            placeholder="Part Number"
                            value={part.partNumber}
                            onChange={(e) => handleChange(index, "partNumber", e.target.value)}
                            className="mb-2"
                        />
                        <Form.Control
                            type="number"
                            placeholder="Quantity"
                            value={part.qty}
                            onChange={(e) => handleChange(index, "qty", e.target.value)}
                            className="mb-2"
                        />
                        <Form.Control
                            as="select"
                            value={part.shopCode}
                            onChange={(e) => handleChange(index, "shopCode", e.target.value)}
                            className="mb-2 text-dark"
                        >
                        <option value="">Select Shop Code</option>
                        {shopOptions.map((shop, i) => (
                            <option key={i} value={shop}>{shop}</option>
                        ))}
                        </Form.Control>
                        <hr />
                    </div>
                    ))}
                    <Button variant="secondary" onClick={addPart} className="mb-2">+ Add More</Button>
                </>
                ) : (
                    <Form.Group>
                        <Form.Label>Upload File</Form.Label>
                        <Form.Control type="file" onChange={handleFileUpload} accept='.xlsx' />
                        <p className="mt-2 mb-2">Download template klik <a href="/assets/form/FormUploadSO.xlsx">disini</a></p>
                    </Form.Group>
                )}
            </Form>
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={handleClose}>Close</Button>
          <Button variant="primary" onClick={saveData}>Save</Button>
        </Modal.Footer>
    </Modal>
    );
  };
  

export default Input_SO;