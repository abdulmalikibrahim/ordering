import React, { useEffect, useState } from "react";
import Index from '../Layout/Index';
import DataTable from "react-data-table-component";
import axios from "axios";
import { useGlobal } from "../GlobalContext";

const Master = ({API_URL}) => {
    const [reloadTable,setreloadTable] = useState(1);
    const { levelAccount } = useGlobal();

    return (
        <Index API_URL={API_URL}>
            <div className="row">
                {
                    levelAccount === "1" &&
                    <div className="col-12">
                        <FormUpload API_URL={API_URL} setreloadTable={setreloadTable} />
                    </div>
                }
                <div className="col-12">
                    <Table API_URL={API_URL} reloadTable={reloadTable} />
                </div>
            </div>
        </Index>
    );
}

const FormUpload = ({ API_URL, setreloadTable }) => {
    const getRandomNumber = () => Math.floor(Math.random() * 101);
    const [file, setFile] = useState(null);
    const [uploading, setUploading] = useState(false);
    const [message, setMessage] = useState("");
  
    // Handler saat file dipilih
    const handleFileChange = (event) => {
        setFile(event.target.files[0]);
    };
  
    // Handler untuk upload file
    const handleUpload = async () => {
        if (!file) {
            setMessage(
            <div className="card bg-danger text-light">
                <div className="card-body p-1 ps-2"><i className="fas fa-info-circle me-2"></i>Pilih file terlebih dahulu!</div>
            </div>);
            return;
        }
    
        setUploading(true);
        setMessage("");
    
        const formData = new FormData();
        formData.append("upload-file", file);
    
        try {
            const response = await axios.post(`${API_URL}/upload_master`, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });
    
            setMessage(
            <div className="card bg-success text-light">
                <div className="card-body p-1 ps-2"><i className="fas fa-check-circle me-2"></i>Upload berhasil!</div>
            </div>);
            setreloadTable(getRandomNumber());
        } catch (error) {
            console.error("Upload gagal:", error);
            setMessage(
            <div className="card bg-danger text-light">
                <div className="card-body p-1 ps-2"><i className="fas fa-info-circle me-2"></i>Upload gagal. Silakan coba lagi.</div>
            </div>);
        } finally {
            setUploading(false);
        }
    };
  
    return (
        <div className="row">
            <div className="col-lg-5">
                <div className="row">
                    <div className="col-lg-10">
                        <input
                            className="form-control"
                            type="file"
                            id="formFile"
                            onChange={handleFileChange}
                            accept=".xlsx"
                        />
                    </div>
                    <div className="col-lg-2">
                        <button
                            className="btn btn-primary"
                            onClick={handleUpload}
                            disabled={uploading}
                        >
                            {uploading ? "Uploading..." : "Upload"}
                        </button>
                    </div>
                </div>
            </div>
            <div className="col-3 text-right">
                {message}
            </div>
        </div>
    );
};

const Table = ({API_URL, reloadTable}) => {
    const [data, setData] = useState([]);
    const [filteredData, setFilteredData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
  
    useEffect(() => {
        fetchData();
    }, [reloadTable]);
  
    const fetchData = async () => {
        try {
            const response = await axios.get(`${API_URL}/get_master`); // Ganti dengan URL API-mu
            setData(response.data);
            setFilteredData(response.data); // Set awal data
            setLoading(false);
        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            setLoading(false);
        }
    };

    // Update data ketika search berubah
    useEffect(() => {
        const result = data.filter((item) =>
            (item.part_number ?? "").toLowerCase().includes(search.toLowerCase()) ||
            (item.part_name ?? "").toLowerCase().includes(search.toLowerCase()) ||
            (item.vendor_code ?? "").toLowerCase().includes(search.toLowerCase()) ||
            (item.vendor_name ?? "").toLowerCase().includes(search.toLowerCase()) ||
            (item.vendor_site ?? "").toLowerCase().includes(search.toLowerCase()) ||
            (item.vendor_site_alias ?? "").toLowerCase().includes(search.toLowerCase()) ||
            (item.job_no ?? "").toLowerCase().includes(search.toLowerCase()) ||
            (item.remark ?? "").toLowerCase().includes(search.toLowerCase())
          );
        setFilteredData(result);
    }, [search, data]);
  
    const columns = [
        { name: "No", selector: (row, index) => index + 1, sortable: true, width: "70px" },
        { name: "Part Number", selector: (row) => row.part_number, sortable: true },
        { name: "Part Name", selector: (row) => row.part_name, sortable: true },
        { name: "Vendor Code", selector: (row) => row.vendor_code, sortable: true },
        { name: "Vendor Name", selector: (row) => row.vendor_name, sortable: true },
        { name: "Vendor Site", selector: (row) => row.vendor_site, sortable: true },
        { name: "Vendor Site Alias", selector: (row) => row.vendor_site_alias, sortable: true },
        { name: "Job No", selector: (row) => row.job_no, sortable: true },
        { name: "Remark", selector: (row) => row.remark, sortable: true },
    ];
  
    return (
        <div className="mt-3">
            <div className="row">
                <div className="col-9">
                    <h2 className="mb-3">Data Master</h2>
                </div>
                <div className="col-3 text-right">
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

export default Master;