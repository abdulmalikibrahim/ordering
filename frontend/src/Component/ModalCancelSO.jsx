import React, { useState } from 'react';
import { useGlobal } from '../GlobalContext';
import axios from 'axios';
import { Button, Modal } from 'react-bootstrap';
import Swal from 'sweetalert2';

const ModalCancelSO = ({ handleCloseCancelModal, showCancelModal, API_URL, setreloadTable, soNumberCancel }) => {
    const {idAccount, setReloadCountRemain} = useGlobal();
    const [keterangan, setKeterangan] = useState('');
  
    const saveData = async () => {
        try {
            const formData = new FormData();
            formData.append("id_account",idAccount);
            formData.append("so_number",soNumberCancel);
            formData.append("keterangan",keterangan);
            await axios.post(`${API_URL}/cancel_approve_so`, formData, {
                withCredentials: true,
            });
            setreloadTable(Math.random() * 10);
            setReloadCountRemain(Math.random() * 10)
            handleCloseCancelModal();
            setKeterangan('');
            Swal.fire("Sukses", "Proses berhasil di lakukan", "success");
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
        <Modal show={showCancelModal} onHide={handleCloseCancelModal}>
            <Modal.Header closeButton>
                <Modal.Title>Reject</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <textarea className='form-control' rows={10} placeholder='Masukkan keterangan' value={keterangan} onChange={(e) => setKeterangan(e.target.value)}></textarea>
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={handleCloseCancelModal}>Close</Button>
                <Button variant="primary" onClick={saveData}>Save</Button>
            </Modal.Footer>
        </Modal>
    );
}

export default ModalCancelSO;