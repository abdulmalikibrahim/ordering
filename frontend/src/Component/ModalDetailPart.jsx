import React from 'react';
import Modal from 'react-bootstrap/Modal';
import Button from 'react-bootstrap/Button';
import DataTable from 'react-data-table-component';

const ModalDetailPart = ({ show, handleClose, data, loadingDetailPart, customStyles }) => {
    const columns = [
        { name: 'No', selector: (row, index) => index + 1, width: '60px' },
        { name: 'Tgl Delivery', selector: row => row.tgl_delivery },
        { name: 'Shop Code', selector: row => row.shop_code },
        { name: 'Part Number', selector: row => row.part_number, width: '150px' },
        { name: 'Part Name', selector: row => row.part_name, width: '250px' },
        { name: 'Job No', selector: row => row.job_no },
        { name: 'Vendor Code', selector: row => row.vendor_code },
        { name: 'Vendor Name', selector: row => row.vendor_name },
        { name: 'Qty Kanban', selector: row => row.qty_kanban },
    ];
  
    return (
        <Modal show={show} onHide={handleClose} size="xl">
            <Modal.Header closeButton>
                <Modal.Title>Detail Data Kanban</Modal.Title>
            </Modal.Header>
            <Modal.Body>
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
                }
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={handleClose}>Tutup</Button>
            </Modal.Footer>
        </Modal>
    );
};

export default ModalDetailPart;