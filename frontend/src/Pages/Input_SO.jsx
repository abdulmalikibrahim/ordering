import React, { useEffect, useState } from 'react';
import axios from 'axios';
import Index from '../Layout/Index';
import Button from "react-bootstrap/Button";
import Swal from 'sweetalert2';
import DataTable from 'react-data-table-component';
import Modal from 'react-bootstrap/Modal';
import Form from 'react-bootstrap/Form';
import ModalDetailPart from '../Component/ModalDetailPart';
import { useGlobal } from '../GlobalContext';

const customStyles = {
    headCells: {
        style: {
            justifyContent: 'center',
            textAlign: 'center',
        },
    },
    cells: {
        style: {
            justifyContent: 'center',
            textAlign: 'center',
            whiteSpace: 'normal',
            overflow: 'visible',
            textOverflow: 'unset',
        },
    },
};

const Input_SO = ({API_URL}) => {
    const [reloadTable,setreloadTable] = useState(1);
    const [show, setShow] = useState(false);
    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);
    const [titleTable, settitleTable] = useState("");
    const [modeInput, setmodeInput] = useState("");
    const [showModalDetail, setShowModalDetail] = useState(false);
    const [detailPart, setDetailPart] = useState([]);
    const [loadingDetailPart, setLoadingDetailPart] = useState(true);
    
    const ShowPart = async (so_number) => {
        setShowModalDetail(true);
        setLoadingDetailPart(true);
        try {
            const response = await axios.get(`${API_URL}/get_detail_part_so?so_number=${so_number}`);
            const data = Array.isArray(response.data) ? response.data : [];
            setDetailPart(data);
        } catch (error) {
            console.error("Error fetching data:", error);
        }
        setLoadingDetailPart(false);
    }

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
        } else if (pageName === 'release_so') {
            settitleTable('Release SO');
            setmodeInput('release_so');
        } 
        setreloadTable(Math.random() * 10);
    },[window.location.pathname])

    return(
        <Index API_URL={API_URL}>
            <div className="row">
                {
                    modeInput !== "release_so" &&
                    <div className="col-12 text-end mb-2">
                        <Button variant="outline-primary" onClick={() => handleShow()}><i className="fas fa-plus"></i> Tambah</Button>
                    </div> 
                }
                <div className="col-12">
                    <Table API_URL={API_URL} reloadTable={reloadTable} setreloadTable={setreloadTable} titleTable={titleTable} modeInput={modeInput} ShowPart={ShowPart} />
                </div>
            </div>
            <FormAdd handleClose={handleClose} show={show} API_URL={API_URL} setreloadTable={setreloadTable} modeInput={modeInput} />

            <ModalDetailPart
                show={showModalDetail}
                handleClose={() => setShowModalDetail(false)}
                data={detailPart}
                loadingDetailPart={loadingDetailPart}
                customStyles={customStyles}
            />
        </Index>
    )
}

const Table = ({API_URL, reloadTable, setreloadTable, titleTable, modeInput, ShowPart}) => {
    const {levelAccount} = useGlobal();
    const [data, setData] = useState([]);
    const [filteredData, setFilteredData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");

    const fetchData = async () => {
        setLoading(true);
        try {
            const response = await axios.get(`${API_URL}/get_data_so?tipe=${modeInput}`, {
                withCredentials: true,
            });
            const fetchedData = Array.isArray(response.data) ? response.data : [];
            setData(fetchedData);
            setFilteredData(fetchedData);
        } catch (error) {
            console.error("Error fetching data:", error);
        }
        setLoading(false); // Tidak perlu di `finally`
    };
  
    useEffect(() => {
        fetchData();
    }, [reloadTable]);

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
        const ClickDelete = (so_number) => {
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
                        formdata.append("so_number",so_number);
                        const response = await axios.post(`${API_URL}/delete_so_number`, formdata, {
                            withCredentials: true,
                        });
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
                <Button variant="danger" className="me-2 btn-sm" title="Hapus" onClick={() => ClickDelete(props.data.so_number)}><i className="fas fa-trash-alt"></i></Button>
            </>
        )
    }

    const ClickRelease = async (so_number) => {
        try {
            const formdata = new FormData();
            formdata.append("so_number",so_number);
            const response = await axios.post(`${API_URL}/release_so`, formdata, {
                withCredentials: true,
            });

            if(response.status === 200){
                Swal.fire("Berhasil!", "Release berhasil di lakukan.", "success");
                setreloadTable(Math.random() * 10);
            }
        } catch (error) {
            if(error.response.status === 400){
                Swal.fire("Error", error.response.data.res, "warning");
            }else{
                Swal.fire("Gagal!", "Terjadi kesalahan saat menghapus.", "error");
                console.error(error)
            }
        }
    }

    const columns = [
        { name: "No", selector: (row, index) => index + 1, sortable: true, width: "70px" },
        { name: "Created", selector: (row) => { const created_time = row.created_time.split(" "); return(<>{created_time[0]}<br />{created_time[1]}</>)  }, sortable: true },
        { name: "SO Number", selector: (row) => row.so_number, sortable: true, width: "250px" },
        { name: "Creator", selector: (row) => row.creator, sortable: true },
        { name: "Dept Creator", selector: (row) => row.dept_creator, sortable: true },
        { name: "Shop Code", selector: (row) => row.shop_code, sortable: true },
        { name: "Total Part", selector: (row) => 
        <Button variant='primary' className='p-1 ps-2 pe-2' title='Click to Show List Part' style={{fontSize: '9pt'}} onClick={() => ShowPart(row.so_number)}>
            {row.total_part}
        </Button>, sortable: true },
        { name: "SPV Sign", selector: (row) => 
        <>
            {row.spv_sign}<br />
            {
                row.spv_sign_time === "" 
                ? <span className="badge badge-warning text-dark mt-1">Not Yet Sign</span> 
                : <span className="badge badge-success text-dark mt-1">{row.spv_sign_time}</span>
            }
        </>, sortable: true },
        { name: "MNG Sign", selector: (row) =>  
        <>
            {row.mng_sign}<br />
            {
                row.mng_sign_time === "" 
                ? <span className="badge badge-warning text-dark mt-1">Not Yet Sign</span> 
                : <span className="badge badge-success text-dark mt-1">{row.mng_sign_time}</span>
            }
        </>, sortable: true },
        {
            name: "Release", selector: (row) =>  
            <>
            {
                row.spv_sign_time && row.mng_sign_time 
                ? 
                (levelAccount === "1" && row.release_sign_time === ""
                    ? <Button variant="success" className="me-2 btn-sm" title="Release" onClick={() => ClickRelease(row.so_number)} style={{fontSize:'9pt'}}><i className="fas fa-check"></i> Release</Button> 
                    : (!row.release_sign_time ? <span className="badge badge-warning text-dark mt-1">Waiting Release</span> : <>{row.release_sign}<br /><span className="badge badge-success text-dark mt-1">{row.release_sign_time}</span></>) 
                )
                : <span className="badge badge-warning text-dark mt-1">Waiting Approval</span> 
            }
            </>, sortable: true
        },
        ...(modeInput !== "release_so" 
            ? [
            { name: "Action", selector: (row) => 
                <ButtonAction data={row} />, 
            sortable: true }
            ] : []
        ),
    ];
  
    return (
        <div className="mt-3">
            <div className="row">
                <div className="col-9">
                    <h2 className="mb-3">{titleTable}</h2>
                </div>
                <div className="col-3 text-end">
                    <div className="input-group mb-2">
                        <input
                            type="text"
                            placeholder="Search..."
                            className="form-control"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                        <Button variant='primary' onClick={() => fetchData()} className='ms-3 rounded'>Refresh</Button>
                    </div>
                </div>
            </div>
            
            <DataTable
                columns={columns}
                data={filteredData}
                progressPending={loading}
                pagination
                highlightOnHover
                customStyles={customStyles}
            />
        </div>
    );
}

const FormAdd = ({ handleClose, show, API_URL, setreloadTable, modeInput }) => {
    const {levelAccount, idAccount} = useGlobal();
    const [file, setFile] = useState(null);
    const [picOptions, setPICOptions] = useState([]);
    const [pic, setpic] = useState("");
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
  
    const handleFileUpload = (event) => {
        setFile(event.target.files[0]);
        {
            levelAccount === "2" && setpic(idAccount);
        }
    };
  
    const saveData = async () => {
        try {
            const formData = new FormData();
            formData.append("modeInput",modeInput);
            formData.append("file", file);
            formData.append("pic", pic);
            await axios.post(`${API_URL}/upload_parts`, formData, {
                withCredentials: true,
            });
            setreloadTable((prev) => prev + 1);
            handleClose();
        } catch (error) {
            if (error.response.status === 400 || error.response.status === 500) {
                Swal.fire("Error", error.response.data.res, "error");
            } else {
                Swal.fire("Error", "Maaf data gagal disimpan", "error");
            }
            console.error(error);
        }
    };
  
    return (
    <Modal show={show} onHide={handleClose}>
        <Modal.Header closeButton>
          <Modal.Title>Form Add Part</Modal.Title>
        </Modal.Header>
        <Modal.Body>
            <Form>
                <div>
                    {
                        levelAccount === "1" &&
                        <Form.Control
                            as="select"
                            value={pic}
                            onChange={(e) => setpic(e.target.value)}
                            className="mb-2 text-dark"
                        >
                        <option value="">Select PIC</option>
                        {picOptions.map((pic, i) => (
                            <option key={i} value={pic.id}>{`${pic.name} (${pic.name_dept})`}</option>
                        ))}
                        </Form.Control>
                    }
                    <Form.Group>
                        <Form.Label>Upload File</Form.Label>
                        <Form.Control type="file" onChange={handleFileUpload} accept='.xlsx' />
                        <p className="mt-2 mb-2">Download template klik <a href="/assets/form/FormUploadSO.xlsx">disini</a></p>
                    </Form.Group>
                </div>
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