import React from 'react';
import Modal from 'react-bootstrap/Modal';
import Button from 'react-bootstrap/Button';
import DataTable from 'react-data-table-component';
import Swal from 'sweetalert2';
import axios from 'axios';
import { useGlobal } from '../GlobalContext';
import he from 'he';

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
                    const response = await axios.post(`${props.API_URL}/delete_part_so`, formdata, {
                        withCredentials: true,
                    });
                    if(response.status === 200){
                        Swal.fire("Berhasil!", "Data telah dihapus.", "success");
                        props.ShowPart(props.data.so_number);
                        props.setreloadTable(Math.random() * 10);
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

const ModalDetailPart = ({ show, handleClose, data, loadingDetailPart, customStyles, API_URL, setreloadTable, ShowPart, tglDelivery, shopCode, soNumber, totalPrice, statusSO, rejectReason }) => {
    const { numberFormat } = useGlobal();
    const columns = [
        { name: 'No', selector: (row, index) => index + 1, width: '60px' },
        { name: 'Part Number', selector: row => row.part_number, width: '150px' },
        { name: 'Part Name', selector: row => row.part_name, width: '250px' },
        { name: 'Job No', selector: row => row.job_no },
        { name: 'Vendor Code', selector: row => row.vendor_code },
        { name: 'Vendor Name', selector: row => row.vendor_name },
        { name: 'Qty', selector: row => row.qty_kanban },
        { name: 'Total Qty', selector: row => row.total_qty },
        { name: 'Price', selector: row => `Rp. ${numberFormat(row.total_price)}`, width: '150px' },
        { name: "Action", selector: (row) => 
            <ButtonAction data={row} API_URL={API_URL} setreloadTable={setreloadTable} ShowPart={ShowPart} />, 
        sortable: true },
    ];
  
    return (
        <Modal show={show} onHide={handleClose} size="xl">
            <Modal.Header closeButton>
                <Modal.Title>Detail Data Kanban</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                {
                    <>
                        <div className="row">
                            <div className="col-lg-6">
                                <h6 style={{ fontFamily:"calibri" }}>
                                    {soNumber}
                                    {
                                        statusSO === "Reject"
                                        ? <span className='ms-1 badge badge-danger'>{statusSO}</span>
                                        : (statusSO === "Release" ? <span className='ms-1 badge badge-success'>{statusSO}</span> : <span className='ms-1 badge badge-warning text-dark'>{statusSO}</span>)
                                    }
                                </h6>
                            </div>
                            <div className="col-lg-6 text-end"><h6 style={{fontFamily:"calibri"}}>{shopCode}, Delivery at : {tglDelivery}</h6></div>
                            <div className="col-lg-6">
                                {
                                    rejectReason && <p>Reason Reject : <div style={{maxWidth:"100%"}}>{rejectReason.split("\n").map((reason,_) => <>{he.decode(reason)}<br /></>)}</div></p>
                                }
                            </div>
                            <div className="col-lg-6 text-end mb-2">
                                <a href={`${API_URL}/export_detail_part?so=${soNumber}`} target='_blank' className="btn btn-sm btn-success">Download</a>
                            </div>
                        </div>
                    </>
                }
                {
                    loadingDetailPart 
                    ?   <div className="text-center">
                            <i className="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    :   <DataTable
                            columns={columns}
                            data={data}
                            highlightOnHover
                            dense
                            customStyles={customStyles}
                        />
                }{
                    <div className="row mt-3">
                        <div className="col-lg-12 text-end"><h4>Total Price : Rp. {numberFormat(totalPrice)}</h4></div>
                    </div>
                }
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={handleClose}>Tutup</Button>
            </Modal.Footer>
        </Modal>
    );
};

export default ModalDetailPart;