import { QRCodeCanvas } from 'qrcode.react';
import { useGlobal } from '../GlobalContext';

const SO_Print = ({ dataSO }) => {
    const { formatDateIndo } = useGlobal();
    const fontsize = {
        fontSize: "8pt"
    }
    return (
        <div className="container">
            <table className="table table-borderless">
                <thead className='text-center'>
                    <tr>
                        <td rowSpan={4} style={{width:"112px", height:"94px"}}><img src="/images/logo-square.webp" style={{width:"100%", height:"100%", borderRadius:"0"}} /></td>
                    </tr>
                    <tr>
                        <td rowSpan={3} style={{width:"580px"}}><h4>FORM REQUEST SPECIAL ORDER</h4></td>
                        <td className='border border-dark bg-secondary text-light'>Received By</td>
                        <td className='border border-dark bg-secondary text-light'>Approve By</td>
                        <td className='border border-dark bg-secondary text-light'>Checked By</td>
                        <td className='border border-dark bg-secondary text-light'>Request By</td>
                    </tr>
                    <tr>
                        <td className='border border-dark'>
                            {
                                dataSO.release_sign && 
                                <QRCodeCanvas
                                    value={dataSO.release_sign}
                                    size={50}
                                    bgColor={"#ffffff"}
                                    fgColor={"#000000"}
                                    level={"H"} // L, M, Q, H (error correction levels)
                                />
                            }
                        </td>
                        <td className='border border-dark'>
                            {
                                dataSO.ttd_mng_sign && 
                                <QRCodeCanvas
                                    value={dataSO.ttd_mng_sign}
                                    size={50}
                                    bgColor={"#ffffff"}
                                    fgColor={"#000000"}
                                    level={"H"} // L, M, Q, H (error correction levels)
                                />
                            }
                        </td>
                        <td className='border border-dark'>
                            {
                                dataSO.ttd_spv_sign && 
                                <QRCodeCanvas
                                    value={dataSO.ttd_spv_sign}
                                    size={50}
                                    bgColor={"#ffffff"}
                                    fgColor={"#000000"}
                                    level={"H"} // L, M, Q, H (error correction levels)
                                />
                            }
                        </td>
                        <td className='border border-dark'>
                            {
                                dataSO.ttd_pic && 
                                <QRCodeCanvas
                                    value={dataSO.ttd_pic}
                                    size={50}
                                    bgColor={"#ffffff"}
                                    fgColor={"#000000"}
                                    level={"H"} // L, M, Q, H (error correction levels)
                                />
                            }
                        </td>
                    </tr>
                    <tr>
                        {
                            console.log(dataSO)
                        }
                        <td className='border border-dark' style={{fontSize:"8pt"}}>{dataSO.release_sign}</td>
                        <td className='border border-dark' style={{fontSize:"8pt"}}>{dataSO.mng_sign}</td>
                        <td className='border border-dark' style={{fontSize:"8pt"}}>{dataSO.spv_sign}</td>
                        <td className='border border-dark' style={{fontSize:"8pt"}}>{dataSO.pic}</td>
                    </tr>
                </thead>
            </table>
            
            <table className="table table-borderless">
                <thead>
                    <tr>
                        <td>
                            <table className="table table-borderless">
                                <thead>
                                    <tr>
                                        <td style={{width:"75px"}}>Registration Code</td>
                                        <td>: {dataSO.data_so.so_number}</td>
                                    </tr>
                                    <tr>
                                        <td>Date Request</td>
                                        <td>: {formatDateIndo(dataSO.data_so.created_time)}</td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td>: { dataSO.data_so.release_sign ? <span className='badge badge-success'>Accepted</span> : (dataSO.data_so.reject_date ? <span className='badge badge-danger'>Reject</span> : <span className='badge badge-warning'>Under Approval</span>) }</td>
                                    </tr>
                                </thead>
                            </table>
                        </td>
                        <td>
                            <table className="table table-borderless">
                                <thead>
                                    <tr>
                                        <td style={{width:"75px"}}>PIC Request</td>
                                        <td>: {dataSO.pic}</td>
                                    </tr>
                                    <tr>
                                        <td>Shop</td>
                                        <td>: {dataSO.data_so.shop_code}</td>
                                    </tr>
                                    {
                                        dataSO.data_so.reject_date
                                        ? 
                                        <>
                                            <tr>
                                                <td>Reject Date</td>
                                                <td>: {formatDateIndo(dataSO.data_so.reject_date)}</td>
                                            </tr>
                                            <tr>
                                                <td>Reject By</td>
                                                <td>: {dataSO.reject_by}</td>
                                            </tr>
                                            <tr>
                                                <td>Reject Reason</td>
                                                <td>: {dataSO.data_so.reject_reason}</td>
                                            </tr>
                                        </>
                                        :
                                        <tr>
                                            <td>Release Date</td>
                                            <td>: {dataSO.data_so.release_sign_time ? formatDateIndo(dataSO.data_so.release_sign_time) : '-'}</td>
                                        </tr>
                                    }
                                </thead>
                            </table>
                        </td>
                    </tr>
                </thead>
            </table>

            <table className="table table-borderless">
                <thead>
                    <tr className='text-center'>
                        <td className="border border-dark" style={fontsize}>No</td>
                        <td className="border border-dark" style={fontsize}>Job. No</td>
                        <td className="border border-dark" style={fontsize}>Part No</td>
                        <td className="border border-dark" style={fontsize}>Part Name</td>
                        <td className="border border-dark" style={fontsize}>Supplier</td>
                        <td className="border border-dark" style={fontsize}>Price/Pcs</td>
                        <td className="border border-dark" style={fontsize}>Qty/Kbn</td>
                        <td className="border border-dark" style={fontsize}>Req. Kbn</td>
                        <td className="border border-dark" style={fontsize}>Req. Pcs</td>
                        <td className="border border-dark" style={fontsize}>Total Price</td>
                        <td className="border border-dark" style={fontsize}>Remarks</td>
                    </tr>
                </thead>
                <tbody>
                    {
                        dataSO.detail_part_so.map((item, index) => {
                            const reqKbn = Number(item.qty_kanban);
                            const stdQty = Number(item.std_qty);
                            const reqPcs = reqKbn * stdQty;

                            return (
                                <tr key={item.id} className='text-center'>
                                    <td className="border border-dark" style={fontsize}>{index + 1}</td>
                                    <td className="border border-dark" style={fontsize}>{item.job_no}</td>
                                    <td className="border border-dark" style={fontsize}>{item.part_number}</td>
                                    <td className="border border-dark" style={fontsize}>{item.part_name}</td>
                                    <td className="border border-dark" style={fontsize}>{item.vendor_name}</td>
                                    <td className="border border-dark" style={fontsize}>{Number(item.price).toLocaleString()}</td>
                                    <td className="border border-dark" style={fontsize}>{stdQty}</td>
                                    <td className="border border-dark" style={fontsize}>{reqKbn}</td>
                                    <td className="border border-dark" style={fontsize}>{reqPcs}</td>
                                    <td className="border border-dark" style={fontsize}>{Number(item.total_price).toLocaleString()}</td>
                                    <td className="border border-dark" style={fontsize}>{item.remark_part}</td>
                                </tr>
                            );
                        })
                    }
                    <tr className='text-center'>
                        <td className="border border-dark" style={fontsize} colSpan={7}>
                            <h6 className='m-0'>Total Part Release Order</h6>
                        </td>
                        <td className="border border-dark" style={fontsize}>
                            <h6 className='m-0'>{Number(dataSO.grand_total_req_kbn).toLocaleString()}</h6>
                        </td>
                        <td className="border border-dark" style={fontsize}>
                            <h6 className='m-0'>{Number(dataSO.grand_total_req_pcs).toLocaleString()}</h6>
                        </td>
                        <td className="border border-dark" style={fontsize}>
                            <h6 className='m-0'>{Number(dataSO.grand_total_price).toLocaleString()}</h6>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    );
}

export default SO_Print;