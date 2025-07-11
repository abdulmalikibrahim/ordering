import React, { useEffect, useState } from "react";
import Index from '../Layout/Index';
import DataTable from "react-data-table-component";
import axios from "axios";
import Button from "react-bootstrap/Button";
import Modal from 'react-bootstrap/Modal';
import Swal from 'sweetalert2';
import SelectDepartement from "../Component/SelectDepartement";

const Account = ({API_URL}) => {
    const [reloadTable,setreloadTable] = useState(1);
    const [show, setShow] = useState(false);
  
    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);
    const [mode,setmode] = useState('add');
    const [dataUpdate,setdataUpdate] = useState([]);

    return (
        <Index API_URL={API_URL}>
            <div className="row">
                <div className="col-12 text-end mb-2">
                    <Button variant="outline-primary" onClick={() => { setmode("add"); setdataUpdate([]); console.log(mode); handleShow(); }}><i className="fas fa-plus"></i> Tambah</Button>
                </div>
                <div className="col-12">
                    <Table API_URL={API_URL} reloadTable={reloadTable} handleShow={handleShow} setmode={setmode} setdataUpdate={setdataUpdate} setreloadTable={setreloadTable} />
                </div>
            </div>
            <FormAddAccount handleClose={handleClose} show={show} API_URL={API_URL} mode={mode} setreloadTable={setreloadTable} dataUpdate={dataUpdate} />
        </Index>
    );
}

const Table = ({API_URL, reloadTable, handleShow, setmode, setdataUpdate, setreloadTable}) => {
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
            const response = await axios.get(`${API_URL}/get_account`);
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
        const lowerSearch = search.toLowerCase();
        const result = data.filter((item) =>
            ["name", "username", "dept", "level", "spv", "mng"].some((key) =>
                (item[key] ?? "").toLowerCase().includes(lowerSearch)
            )
        );
        setFilteredData(result);
    }, [search, data]);

    const ButtonAction = ({...props}) => {

        const ClickEdit = () => {
            setmode('update')
            handleShow();
            setdataUpdate(props.data)
        }

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
                        const response = await axios.post(`${API_URL}/delete_account`, formdata);
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

        const ClickResetPassword = async (id) => {
            try {
                const formdata = new FormData();
                formdata.append("id",id);
                const response = await axios.post(`${API_URL}/reset_password`, formdata);
                if(response.status === 200){
                    Swal.fire("Berhasil!", "Password berhasil reset menjadi Astra123", "success");
                }
            } catch (error) {
                Swal.fire("Gagal!", "Terjadi kesalahan saat menghapus.", "error");
                console.error(error);
                
            }
        };

        return(
            <>
                <Button variant="primary" className="me-2 btn-sm" onClick={ClickEdit} title="Edit"><i className="fas fa-pencil-alt"></i></Button>
                <Button variant="danger" className="me-2 btn-sm" title="Hapus" onClick={() => ClickDelete(props.data.id)}><i className="fas fa-trash-alt"></i></Button>
                <Button variant="success" className="btn-sm" title="Reset Password" onClick={() => ClickResetPassword(props.data.id)}><i className="fas fa-key"></i></Button>
            </>
        )
    }

  
    const columns = [
        { name: "No", selector: (row, index) => index + 1, sortable: true, width: "70px" },
        { name: "Nama", selector: (row) => row.name, sortable: true },
        { name: "Username", selector: (row) => row.username, sortable: true },
        { name: "Email", selector: (row) => row.email, sortable: true },
        { name: "Departement", selector: (row) => row.dept, sortable: true },
        { name: "Level", selector: (row) => row.level, sortable: true },
        { name: "Action", selector: (row) => 
            <ButtonAction data={row} />, 
        sortable: true },
    ];
  
    return (
        <div className="mt-3">
            <div className="row">
                <div className="col-9">
                    <h2 className="mb-3">Data Account</h2>
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

const FormAddAccount = ({ handleClose, show, API_URL, mode, setreloadTable, dataUpdate }) => {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [name, setName] = useState('');
    const [deptList, setDeptList] = useState(['']);
    const [level, setLevel] = useState(1);
    const [email, setEmail] = useState('');
    const [idUpdate, setidUpdate] = useState(0);

    const dataLevel = ["Admin", "User", "Supervisor", "Manager"];
    const requiredSymbol = <span className="text-danger">*</span>;

    const clearForm = () => {
        if (dataUpdate) {
            setUsername(dataUpdate.username || '');
            setPassword(dataUpdate.password || '');
            setName(dataUpdate.name || '');
            setEmail(dataUpdate.email || '');
            setLevel(dataUpdate.id_level || 1);
            setidUpdate(dataUpdate.id || 0);
            try {
                const parsedDept = JSON.parse(dataUpdate.id_dept);
                setDeptList(Array.isArray(parsedDept) ? parsedDept : []);
            } catch (e) {
                console.error("Gagal parsing id_dept:", e);
                setDeptList([]);
            }
        }
    };

    const SaveData = async () => {
        try {
            const formdata = new FormData();
            formdata.append('id_update', idUpdate);
            formdata.append('name', name);
            formdata.append('username', username);
            formdata.append('password', password);
            formdata.append('email', email);
            formdata.append('level', level);
            formdata.append('mode', mode);
            formdata.append('dept', JSON.stringify(deptList)); // Simpan array

            const response = await axios.post(`${API_URL}/save_account`, formdata);
            if (response.status === 200) {
                Swal.fire("Sukses", "Data berhasil disimpan", "success");
                setreloadTable(Math.random() * 10);
                clearForm();
                handleClose();
            }
        } catch (error) {
            Swal.fire("Error", error?.response?.data?.res || "Gagal simpan data", "error");
        }
    };

    const addDept = () => {
        setDeptList([...deptList, '']);
    };

    const updateDept = (index, value) => {
        const newDeptList = [...deptList];
        newDeptList[index] = value;
        setDeptList(newDeptList);
    };

    const removeDept = (index) => {
        const newDeptList = [...deptList];
        newDeptList.splice(index, 1);
        setDeptList(newDeptList.length ? newDeptList : ['']);
    };

    useEffect(() => {
        clearForm();
        console.log(mode)
        if(mode == "add") {
            setUsername('');
            setPassword('');
            setName('');
            setEmail('');
            setLevel(1);
            setidUpdate(0);
            setDeptList(['']);
        }
    }, [dataUpdate]);

    return (
        <Modal show={show} onHide={handleClose}>
            <Modal.Header closeButton>
                <Modal.Title>Form Account</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <p className="mb-1">Nama {requiredSymbol}</p>
                <input type="text" className="mb-2 form-control" value={name} onChange={(e) => setName(e.target.value)} />
                
                <p className="mb-1">Username {requiredSymbol}</p>
                <input type="text" className="mb-2 form-control" value={username} onChange={(e) => setUsername(e.target.value)} />
                
                <p className="mb-1">Email {requiredSymbol}</p>
                <input type="email" className="mb-2 form-control" value={email} onChange={(e) => setEmail(e.target.value)} />

                {mode === "add" && (
                    <>
                        <p className="mb-1">Password {requiredSymbol}</p>
                        <input type="password" className="mb-2 form-control" value={password} onChange={(e) => setPassword(e.target.value)} />
                    </>
                )}

                <p className="mb-1">Departemen {requiredSymbol}</p>
                {deptList.map((dept, index) => (
                    <div key={index} className="d-flex mb-2 align-items-center">
                        <SelectDepartement
                            deptName={dept}
                            API_URL={API_URL}
                            setDept={(val) => updateDept(index, val)}
                        />
                        {deptList.length > 1 && (
                            <Button variant="danger" size="sm" className="ms-2" onClick={() => removeDept(index)}>−</Button>
                        )}
                    </div>
                ))}
                <Button variant="outline-primary" size="sm" onClick={addDept}>+ Tambah Departemen</Button>

                <p className="mb-1 mt-3">Level {requiredSymbol}</p>
                <select className="mb-2 form-control text-dark" value={level} onChange={(e) => setLevel(parseInt(e.target.value, 10))}>
                    {dataLevel.map((value, index) => (
                        <option key={index} value={index + 1}>{value}</option>
                    ))}
                </select>
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={handleClose}>Close</Button>
                <Button variant="primary" onClick={SaveData}>Save Changes</Button>
            </Modal.Footer>
        </Modal>
    );
};

export default Account;