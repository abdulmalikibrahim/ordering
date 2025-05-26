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
import SelectBoxPIC from '../Component/SelectBoxPIC';
import ModalPrintSO from '../Component/ModalPrintSO';

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
    const [showModalPrint, setShowModalPrint] = useState(false);
    const [detailPart, setDetailPart] = useState([]);
    const [tglDelivery, setTglDelivery] = useState("");
    const [shopCodeDetail, setShopCodeDetail] = useState("");
    const [totalPrice, setTotalPrice] = useState("");
    const [statusSO, setStatusSO] = useState("");
    const [rejectReason, setRejectReason] = useState("");
    const [soNumberPick, setSoNumberPick] = useState("");
    const [loadingDetailPart, setLoadingDetailPart] = useState(true);
    
    const ShowPart = async (so_number) => {
        setShowModalDetail(true);
        setLoadingDetailPart(true);
        try {
            const response = await axios.get(`${API_URL}/get_detail_part_so?so_number=${so_number}`);
            const data = Array.isArray(response.data.data) ? response.data.data : [];
            setShopCodeDetail(response.data.shop_code);
            setTglDelivery(response.data.tgl_delivery);
            setSoNumberPick(so_number);
            setTotalPrice(response.data.grandTotal);
            setStatusSO(response.data.status_so);
            setRejectReason(response.data.reason_reject);
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
        } else if (pageName === 'delete_so') {
            settitleTable('Delete SO');
            setmodeInput('delete_so');
        } else if (pageName === 'reject_so') {
            settitleTable('Reject SO');
            setmodeInput('reject_so');
        } else if (pageName === 'need_release') {
            settitleTable('Remain Release');
            setmodeInput('need_release');
        } 
        setreloadTable(Math.random() * 10);
    },[window.location.pathname])

    return(
        <Index API_URL={API_URL}>
            <div className="row">
                {
                    modeInput !== "release_so" && modeInput !== "delete_so" && modeInput !== "reject_so" &&
                    <div className="col-12 text-end mb-2">
                        <Button variant="outline-primary" onClick={() => handleShow()}><i className="fas fa-plus"></i> Tambah</Button>
                    </div> 
                }
                <div className="col-12">
                    <Table 
                        API_URL={API_URL} 
                        reloadTable={reloadTable} 
                        setreloadTable={setreloadTable} 
                        titleTable={titleTable} 
                        modeInput={modeInput} 
                        ShowPart={ShowPart} 
                        setShowModalPrint={setShowModalPrint} 
                        setSoNumberPick={setSoNumberPick}
                    />
                </div>
            </div>
            <FormAdd handleClose={handleClose} show={show} API_URL={API_URL} setreloadTable={setreloadTable} modeInput={modeInput} />

            <ModalDetailPart
                show={showModalDetail}
                handleClose={() => setShowModalDetail(false)}
                data={detailPart}
                loadingDetailPart={loadingDetailPart}
                customStyles={customStyles}
                API_URL={API_URL}
                ShowPart={ShowPart}
                setreloadTable={setreloadTable}
                tglDelivery={tglDelivery}
                shopCode={shopCodeDetail}
                soNumber={soNumberPick}
                totalPrice={totalPrice}
                statusSO={statusSO}
                rejectReason={rejectReason}
            />

            <ModalPrintSO
                show={showModalPrint}
                handleClose={() => setShowModalPrint(false)}
                API_URL={API_URL}
                soNumber={soNumberPick}
            />
        </Index>
    )
}

const Table = ({API_URL, reloadTable, setreloadTable, titleTable, modeInput, ShowPart, setShowModalPrint, setSoNumberPick}) => {
    const {setReloadCountRemain} = useGlobal();
    const {levelAccount} = useGlobal();
    const [data, setData] = useState([]);
    const [dataMaster, setDataMaster] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filterSONumber, setFilterSONumber] = useState('');
    const [filterCreator, setFilterCreator] = useState('');
    const [filterDeptCreator, setFilterDeptCreator] = useState('');
    const [filterShopCode, setFilterShopCode] = useState('');
    const [filterDetailing, setFilterDetailing] = useState('')
    const [filterCreatedDate, setFilterCreatedDate] = useState('');
    const [filterSPVSign, setFilterSPVSign] = useState('');
    const [filterMNGSign, setFilterMNGSign] = useState('');
    const [filterRelease, setFilterRelease] = useState('');

    const filteredData = data.filter(item =>
        item.so_number.toLowerCase().includes(filterSONumber.toLowerCase()) &&
        item.creator.toLowerCase().includes(filterCreator.toLowerCase()) &&
        item.dept_creator.toLowerCase().includes(filterDeptCreator.toLowerCase()) &&
        item.shop_code.toLowerCase().includes(filterShopCode.toLowerCase()) &&
        item.detailing_part.toLowerCase().includes(filterDetailing.toLowerCase()) &&
        item.created_time.toLowerCase().includes(filterCreatedDate.toLowerCase()) &&
        (item.spv_sign.toLowerCase().includes(filterSPVSign.toLowerCase()) || item.spv_sign_time.toLowerCase().includes(filterSPVSign.toLowerCase())) &&
        (item.mng_sign.toLowerCase().includes(filterSPVSign.toLowerCase()) || item.mng_sign_time.toLowerCase().includes(filterMNGSign.toLowerCase())) &&
        (item.release_sign.toLowerCase().includes(filterSPVSign.toLowerCase()) || item.release_sign_time.toLowerCase().includes(filterRelease.toLowerCase()))
    );

    const fetchData = async () => {
        setLoading(true);
        try {
            const response = await axios.get(`${API_URL}/get_data_so?tipe=${modeInput}`, {
                withCredentials: true,
            });
            const fetchedData = Array.isArray(response.data) ? response.data : [];
            setData(fetchedData);
            setReloadCountRemain(Math.random() * 10);
        } catch (error) {
            console.error("Error fetching data:", error);
        }
        setLoading(false); // Tidak perlu di `finally`
    };

    const fetchDataMaster = async () => {
        try {
            const response = await axios.get(`${API_URL}/data_part_master`, {
                withCredentials: true,
            });
            const fetchedData = Array.isArray(response.data) ? response.data : [];
            setDataMaster(fetchedData);
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    };

    const showModalPrintSO = (so_number) => {
        setShowModalPrint(true);
        setSoNumberPick(so_number);
    }
  
    useEffect(() => {
        fetchData();
        fetchDataMaster();
    }, [reloadTable]);

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
        
        const printUrl = `${API_URL}/print_so?so_number=${props.data.so_number}`;

        return(
            <div style={{width:"130px"}}>
                <Button variant="danger" className="me-2 btn-sm" title="Hapus" onClick={() => ClickDelete(props.data.so_number)}><i className="fas fa-trash-alt"></i></Button>
                
                <Button variant="info" className="me-2 btn-sm" onClick={() => window.open(printUrl, '_blank')} title='Print'><i className="fas fa-print"></i></Button>

                <Button variant="primary" className="me-2 btn-sm" onClick={() => showModalPrintSO(props.data.so_number)} title='Detail'><i className="fas fa-circle-info"></i></Button>
            </div>
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
    const conditionalRowStyles = [
        {
            when: row => row.status_so === "reject",
            style: {
                backgroundColor: '#ffcdcd', // kuning muda
                color: '#856404',           // coklat untuk teks (biar kelihatan)
            },
        },
    ];
    const columns = [
        { 
            name: (
                <div className='pt-2 pb-2'>
                    <div>Created Date</div>
                    <div>
                        <input
                            type="text"
                            placeholder="Search..."
                            className="form-control mt-1 p-1 text-center"
                            style={{ height: '26px', fontSize:'9pt' }}
                            onChange={e => setFilterCreatedDate(e.target.value)}
                        />
                    </div>
                </div>
            ), selector: (row) => { 
                const created_time = row.created_time.split(" "); 
                return(<>{created_time[0]}<br />{created_time[1]}</>)  
            }, sortable: false 
        },
        { 
            name: (
                <div className='pt-2 pb-2'>
                    <div>SO Number</div>
                    <div>
                        <input
                            type="text"
                            placeholder="Search..."
                            className="form-control mt-1 p-1 text-center"
                            style={{ height: '26px', fontSize:'9pt' }}
                            onChange={e => setFilterSONumber(e.target.value)}
                        />
                    </div>
                </div>
            ), selector: (row) => row.so_number, sortable: false, width: "250px" 
        },
        { 
            name: (
                <div className='pt-2 pb-2'>
                    <div>Creator</div>
                    <div>
                        <input
                            type="text"
                            placeholder="Search..."
                            className="form-control mt-1 p-1 text-center"
                            style={{ height: '26px', fontSize:'9pt' }}
                            onChange={e => setFilterCreator(e.target.value)}
                        />
                    </div>
                </div>
            ), selector: (row) => row.creator, sortable: false 
        },
        { 
            name: (
                <div className='pt-2 pb-2'>
                    <div>Dept Creator</div>
                    <div>
                        <input
                            type="text"
                            placeholder="Search..."
                            className="form-control mt-1 p-1 text-center"
                            style={{ height: '26px', fontSize:'9pt' }}
                            onChange={e => setFilterDeptCreator(e.target.value)}
                        />
                    </div>
                </div>
            ), selector: (row) => row.dept_creator, sortable: false 
        },
        { 
            name: (
                <div className='pt-2 pb-2'>
                    <div>Shop Code</div>
                    <div>
                        <input
                            type="text"
                            placeholder="Search..."
                            className="form-control mt-1 p-1 text-center"
                            style={{ height: '26px', fontSize:'9pt' }}
                            onChange={e => setFilterShopCode(e.target.value)}
                        />
                    </div>
                </div>
            ), selector: (row) => row.shop_code, sortable: false 
        },
        { 
            name: (
                <div className='pt-2 pb-2'>
                    <div>Total Part</div>
                    <div>
                        <input
                            list="master_part"
                            type="text"
                            placeholder="Search..."
                            className="form-control mt-1 p-1 text-center"
                            style={{ height: '26px', fontSize:'9pt' }}
                            onChange={e => setFilterDetailing(e.target.value)}
                        />
                    </div>
                    <datalist id="master_part">
                        {
                            dataMaster.map((item, index) => (
                                <option value={item} key={index} />
                            ))
                        }
                    </datalist>
                </div>
            ), selector: (row) =>
                <div>
                    <Button 
                        variant='primary'
                        className='p-1 ps-2 pe-2'
                        title='Click to Show List Part'
                        style={{fontSize: '9pt'}}
                        onClick={() => ShowPart(row.so_number)}
                    >
                        {row.total_part}
                    </Button>
                </div>
            , sortable: false 
        },
        ...(modeInput !== "reject_so")
        ? [
            { 
                name: (
                    <div className='pt-2 pb-2'>
                        <div>SPV Sign</div>
                        <div>
                            <input
                                type="text"
                                placeholder="Search..."
                                className="form-control mt-1 p-1 text-center"
                                style={{ height: '26px', fontSize:'9pt' }}
                                onChange={e => setFilterSPVSign(e.target.value)}
                            />
                        </div>
                    </div>
                ), selector: (row) => 
                    <>
                        {row.spv_sign}<br />
                        {
                            row.spv_sign_time === "" ? (
                                <span className="badge badge-warning text-dark mt-1">Not Yet Sign</span>
                            ) : row.spv_sign_time.includes("reject") ? (
                                <span className="badge badge-danger text-dark mt-1">{row.spv_sign_time}</span>
                            ) : (
                                <span className="badge badge-success text-dark mt-1">{row.spv_sign_time}</span>
                            )
                        }
                    </>
                , sortable: false 
            },
            { 
                name: (
                    <div className='pt-2 pb-2'>
                        <div>MNG Sign</div>
                        <div>
                            <input
                                type="text"
                                placeholder="Search..."
                                className="form-control mt-1 p-1 text-center"
                                style={{ height: '26px', fontSize:'9pt' }}
                                onChange={e => setFilterMNGSign(e.target.value)}
                            />
                        </div>
                    </div>
                ), selector: (row) =>  
                    <>
                        {row.mng_sign}<br />
                        {
                            row.mng_sign_time === "" ? (
                                <span className="badge badge-warning text-dark mt-1">Not Yet Sign</span>
                            ) : row.mng_sign_time.includes("reject") ? (
                                <span className="badge badge-danger text-dark mt-1">{row.mng_sign_time}</span>
                            ) : (
                                <span className="badge badge-success text-dark mt-1">{row.mng_sign_time}</span>
                            )
                        }
                    </>
                , sortable: false 
            },
            {
                name: (
                    <div className='pt-2 pb-2'>
                        <div>Release</div>
                        <div>
                            <input
                                type="text"
                                placeholder="Search..."
                                className="form-control mt-1 p-1 text-center"
                                style={{ height: '26px', fontSize:'9pt' }}
                                onChange={e => setFilterRelease(e.target.value)}
                            />
                        </div>
                    </div>
                    ), selector: (row) =>  
                <>
                {
                    row.spv_sign_time && row.mng_sign_time
                    ? 
                    (levelAccount === "1" && row.release_sign_time === ""
                        ? 
                        (row.reject_date 
                            ? <span className="badge badge-danger text-dark mt-1">Canceled</span> 
                            : <Button 
                                variant="success" 
                                className="me-2 btn-sm" 
                                title="Release" 
                                onClick={() => ClickRelease(row.so_number)} 
                                style={{fontSize:'9pt'}}
                                >
                                    <i className="fas fa-check"></i> Release
                                </Button>
                        ) 
                        : (!row.release_sign_time ? <span className="badge badge-warning text-dark mt-1">Waiting Release</span> : <>{row.release_sign}<br /><span className="badge badge-success text-dark mt-1">{row.release_sign_time}</span></>) 
                    )
                    : <span className="badge badge-warning text-dark mt-1">Waiting Approval</span> 
                }
                </>, sortable: false
            },
        ] 
        : [
            { 
                name: (
                    <div className='pt-2 pb-2'>
                        <div>Reject By</div>
                        <div>
                            <input
                                type="text"
                                placeholder="Search..."
                                className="form-control mt-1 p-1 text-center"
                                style={{ height: '26px', fontSize:'9pt' }}
                                onChange={e => setFilterSPVSign(e.target.value)}
                            />
                        </div>
                    </div>
                ), selector: (row) => 
                    <>
                        {row.reject_by}<br />
                        <span className="badge badge-danger text-dark mt-1">{row.reject_date}</span>
                    </>
                , sortable: false 
            },
            { 
                name: (
                    <div className='pt-2 pb-2'>
                        <div>Reason Reject</div>
                    </div>
                ), selector: (row) => 
                    <div className='text-left' style={{whiteSpace: 'normal', wordWrap: 'break-word', overflowWrap: 'break-word'}}>{row.reject_reason}</div>
                , sortable: false 
            },
        ],
        ...(modeInput !== "delete_so" 
            ? [
            { 
                name: "Action", 
                selector: (row) => <ButtonAction data={row} />, 
                sortable: true,
                width: "150px"
            }
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
                    <Button variant='primary' onClick={() => fetchData()} className='ms-3 rounded'>Refresh</Button>
                </div>
            </div>

            <DataTable
                columns={columns}
                data={filteredData}
                progressPending={loading}
                pagination
                highlightOnHover
                customStyles={customStyles}
                conditionalRowStyles={conditionalRowStyles}
                noDataComponent={<div style={{ padding: '20px' }}>Tidak ada data ditemukan</div>}
                persistTableHead
            />
        </div>
    );
}

const FormAdd = ({ handleClose, show, API_URL, setreloadTable, modeInput }) => {
    const {levelAccount, idAccount} = useGlobal();
    const [file, setFile] = useState(null);
    const [pic, setpic] = useState("");
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
                        <SelectBoxPIC setpic={setpic} pic={pic} API_URL={API_URL} />
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