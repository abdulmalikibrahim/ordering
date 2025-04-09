import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import 'bootstrap/dist/css/bootstrap.min.css';
import Home from './Pages/Home';
import Login from './Pages/Login';
import Master from './Pages/Master';
import Account from './Pages/Account';
import Departement from './Pages/Departement';
import Input_SO from './Pages/Input_SO';

const root = ReactDOM.createRoot(document.getElementById('root'));

const App = () => {
  const API_URL = process.env.REACT_APP_API_URL;
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Login API_URL={API_URL} />} />
        <Route path="/home" element={<Home API_URL={API_URL} />} />
        <Route path="/master" element={<Master API_URL={API_URL} />} />
        <Route path="/account" element={<Account API_URL={API_URL} />} />
        <Route path="/db_dept" element={<Departement API_URL={API_URL} />} />
        <Route path="/input_so" element={<Input_SO API_URL={API_URL} type={"input_so"} />} />
        <Route path="/reduce_order" element={<Input_SO API_URL={API_URL} type={"reduce_order"} />} />
        <Route path="/additional_order" element={<Input_SO API_URL={API_URL} type={"additional_order"} />} />
      </Routes>
    </Router>
  );
}


root.render(
  <App />
);

