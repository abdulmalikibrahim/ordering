import React from 'react';
import { Link } from 'react-router-dom';

const SideBar = () => {
    const path = window.location.pathname; // "/master"
    const pageName = path.split("/")[1]; // "master"
    return (
    <nav className="sidebar sidebar-offcanvas" id="sidebar">
        <ul className="nav">
            <li className="nav-item nav-profile">
                <a href="#" className="nav-link">
                <div className="nav-profile-image">
                    <img src="assets/images/faces/face1.jpg" alt="profile" />
                    <span className="login-status online"></span>
                </div>
                <div className="nav-profile-text d-flex flex-column">
                    <span className="font-weight-bold mb-2">David Grey. H</span>
                    <span className="text-secondary text-small">Project Manager</span>
                </div>
                <i className="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                </a>
            </li>
            <li className={pageName === 'home' ? "nav-item active" : "nav-item"}>
                <Link className="nav-link" to="/home">
                    <span className="menu-title">Dashboard</span>
                    <i className="mdi mdi-home menu-icon"></i>
                </Link>
            </li>
            <li className={pageName.includes('master') || pageName.includes('account') || pageName.includes('db_dept') ? "nav-item active" : "nav-item"}>
                <a className="nav-link" data-bs-toggle="collapse" href="#database" aria-expanded="false" aria-controls="database">
                    <span className="menu-title">Database</span>
                    <i className="mdi mdi-database menu-icon"></i>
                </a>
                <div className={pageName.includes('master') || pageName.includes('account') || pageName.includes('db_dept') ? "collapse show" : "collapse"} id="database">
                    <ul className="nav flex-column sub-menu">
                        <li className={pageName === 'master' ? "nav-item active" : "nav-item"}>
                            <Link className="nav-link" to="/master">
                                <span className="menu-title">Master Part</span>
                            </Link>
                        </li>
                        <li className={pageName === 'account' ? "nav-item active" : "nav-item"}>
                            <Link className="nav-link" to="/account">
                                <span className="menu-title">Account</span>
                            </Link>
                        </li>
                        <li className={pageName === 'db_dept' ? "nav-item active" : "nav-item"}>
                            <Link className="nav-link" to="/db_dept">
                                <span className="menu-title">Dept</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </li>
            <li className={pageName.includes('input_so') || pageName.includes('reduce_order') || pageName.includes('additional_order') ? "nav-item active" : "nav-item"}>
                <a className="nav-link" data-bs-toggle="collapse" href="#input_so" aria-expanded="false" aria-controls="input_so">
                    <span className="menu-title">Input SO</span>
                    <i className="mdi mdi-note-plus menu-icon"></i>
                </a>
                <div className={pageName.includes('input_so') || pageName.includes('reduce_order') || pageName.includes('additional_order') ? "collapse show" : "collapse"} id="input_so">
                    <ul className="nav flex-column sub-menu">
                        <li className={pageName === 'input_so' ? "nav-item active" : "nav-item"}>
                            <Link className="nav-link" to="/input_so">
                                <span className="menu-title">Upload SO</span>
                            </Link>
                        </li>
                        <li className={pageName === 'reduce_order' ? "nav-item active" : "nav-item"}>
                            <Link className="nav-link" to="/reduce_order">
                                <span className="menu-title">Reduce Order</span>
                            </Link>
                        </li>
                        <li className={pageName === 'additional_order' ? "nav-item active" : "nav-item"}>
                            <Link className="nav-link" to="/additional_order">
                                <span className="menu-title">Additional Order</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </li>
            <li className="nav-item">
                <a className="nav-link" data-bs-toggle="collapse" href="#forms" aria-expanded="false" aria-controls="forms">
                    <span className="menu-title">Forms</span>
                    <i className="mdi mdi-format-list-bulleted menu-icon"></i>
                </a>
                <div className="collapse" id="forms">
                    <ul className="nav flex-column sub-menu">
                        <li className="nav-item">
                        <a className="nav-link" href="pages/forms/basic_elements.html">Form Elements</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li className="nav-item">
                <a className="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                <span className="menu-title">Charts</span>
                <i className="mdi mdi-chart-bar menu-icon"></i>
                </a>
                <div className="collapse" id="charts">
                <ul className="nav flex-column sub-menu">
                    <li className="nav-item">
                    <a className="nav-link" href="pages/charts/chartjs.html">ChartJs</a>
                    </li>
                </ul>
                </div>
            </li>
            <li className="nav-item">
                <a className="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
                <span className="menu-title">Tables</span>
                <i className="mdi mdi-table-large menu-icon"></i>
                </a>
                <div className="collapse" id="tables">
                <ul className="nav flex-column sub-menu">
                    <li className="nav-item">
                    <a className="nav-link" href="pages/tables/basic-table.html">Basic table</a>
                    </li>
                </ul>
                </div>
            </li>
            <li className="nav-item">
                <a className="nav-link" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                <span className="menu-title">User Pages</span>
                <i className="menu-arrow"></i>
                <i className="mdi mdi-lock menu-icon"></i>
                </a>
                <div className="collapse" id="auth">
                <ul className="nav flex-column sub-menu">
                    <li className="nav-item">
                    <a className="nav-link" href="pages/samples/blank-page.html"> Blank Page </a>
                    </li>
                    <li className="nav-item">
                    <a className="nav-link" href="pages/samples/login.html"> Login </a>
                    </li>
                    <li className="nav-item">
                    <a className="nav-link" href="pages/samples/register.html"> Register </a>
                    </li>
                    <li className="nav-item">
                    <a className="nav-link" href="pages/samples/error-404.html"> 404 </a>
                    </li>
                    <li className="nav-item">
                    <a className="nav-link" href="pages/samples/error-500.html"> 500 </a>
                    </li>
                </ul>
                </div>
            </li>
            <li className="nav-item">
                <a className="nav-link" href="docs/documentation.html" target="_blank">
                <span className="menu-title">Documentation</span>
                <i className="mdi mdi-file-document-box menu-icon"></i>
                </a>
            </li>
        </ul>
    </nav>
    );
}

export default SideBar;