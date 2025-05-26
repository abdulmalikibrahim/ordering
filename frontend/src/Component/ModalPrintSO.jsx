import { useEffect, useState } from 'react';
import Modal from 'react-bootstrap/Modal';
import Button from 'react-bootstrap/Button';
import axios from 'axios';
import SO_Print from './SO_Print';

const ModalPrintSO = ({ show, handleClose, API_URL, soNumber }) => {
    const [loadingDetailPart, setLoadingDetailPart] = useState(true);
    const [dataSO, setDataSO] = useState(null);

    useEffect(() => {
        if (show && soNumber) {
            setLoadingDetailPart(true);
            axios.get(`${API_URL}/get_detail_so?so_number=${soNumber}`)
                .then((res) => {
                    setDataSO(res.data);
                })
                .catch((err) => {
                    console.error('Error fetching data:', err);
                })
                .finally(() => {
                    setLoadingDetailPart(false);
                });
        }
    }, [show, soNumber]);

    const printUrl = `${API_URL}/print_so?so_number=${soNumber}`;

    return (
        <Modal show={show} onHide={handleClose} size="xl">
            <Modal.Header closeButton>
                <Modal.Title>Print SO</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                {loadingDetailPart ? (
                    <div className="text-center">
                        <i className="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                ) : (
                    <SO_Print dataSO={dataSO} />
                )}
            </Modal.Body>
            <Modal.Footer>
                <Button variant="info" onClick={() => window.open(printUrl, '_blank')}>Print</Button>
                <Button variant="secondary" onClick={handleClose}>Tutup</Button>
            </Modal.Footer>
        </Modal>
    );
};

export default ModalPrintSO;
