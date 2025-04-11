import React, { useEffect, useState } from 'react';
import axios from 'axios';
import Index from '../Layout/Index';
import Button from "react-bootstrap/Button";
import Swal from 'sweetalert2';
import DataTable from 'react-data-table-component';
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

const RemainApprove = ({API_URL}) => {
    const [reloadTable,setreloadTable] = useState(1);
    const [showModalDetail, setShowModalDetail] = useState(false);
    const [detailPart, setDetailPart] = useState([]);
    const [titleTable, settitleTable] = useState("");
    const [modeInput, setmodeInput] = useState("");
    const [loadingDetailPart, setLoadingDetailPart] = useState(true);
    
    useEffect(() => {
        const path = window.location.pathname;
        const pageName = path.split("/")[1];
        if (pageName === 'remain_approve') {
            settitleTable('SO Remain Approve');
            setmodeInput('remain');
        } else if (pageName === 'already_approve') {
            settitleTable('SO Already Approve');
            setmodeInput('approved');
        }
        setreloadTable(Math.random() * 10);
    },[window.location.pathname])

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
        setreloadTable(Math.random() * 10);
        //eslint-disable-next-line
    },[])

    return(
        <Index API_URL={API_URL}>
            <div className="row">
                <div className="col-12">
                    <Table API_URL={API_URL} reloadTable={reloadTable} setreloadTable={setreloadTable} ShowPart={ShowPart} titleTable={titleTable} modeInput={modeInput} />
                </div>
            </div>

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

const Table = ({API_URL, reloadTable, setreloadTable, ShowPart, titleTable, modeInput}) => {
    const {setReloadCountRemain} = useGlobal();
    const [data, setData] = useState([]);
    const [filteredData, setFilteredData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");

    const fetchData = async () => {
        setLoading(true);
        try {
            const response = await axios.get(`${API_URL}/get_data_so_management?tipe=${modeInput}`, {
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
        const ClickApprove = async (so_number) => {
            try {
                const formdata = new FormData();
                formdata.append("so_number",so_number);
                const response = await axios.post(`${API_URL}/approve_so`, formdata, {
                    withCredentials: true,
                });
                if(response.status === 200){
                    Swal.fire("Berhasil!", "Approval berhasil di lakukan.", "success");
                    setreloadTable(Math.random() * 10);
                    setReloadCountRemain(Math.random() * 10);
                }
            } catch (error) {
                Swal.fire("Gagal!", "Terjadi kesalahan saat approve.", "error");
                console.error(error);
                
            }
        };

        return(
            <>
                <Button variant="info" className="me-2 btn-sm" title="Approve" onClick={() => ClickApprove(props.data.so_number)}><i className="fas fa-thumbs-up me-1"></i> Approve</Button>
            </>
        )
    }

    const columns = [
        { name: "No", selector: (row, index) => index + 1, sortable: true, width: "70px" },
        { name: "Created", selector: (row) => row.created_time, sortable: true },
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
                !row.release_sign_time 
                ? <span className="badge badge-warning text-dark mt-1">Waiting Release</span> 
                : <>{row.release_sign}<br /><span className="badge badge-success text-dark mt-1">{row.release_sign_time}</span></>
            }
            </>, sortable: true
        },
        ...(modeInput === "remain" 
            ? [
                { name: "Action", selector: (row) => 
                    <ButtonAction data={row} />, 
                sortable: true },
            ] : []
        )
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

export default RemainApprove;