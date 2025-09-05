import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import MyProducts from './MyProducts';
import ProductCreate from './ProductCreate';
import ProductEdit from './ProductEdit';
import './AccountLayout.css';

const AccountLayout: React.FC = () => {
  return (
    <div className="account-layout">
      <div className="account-container">
        <Routes>
          <Route path="/" element={<Navigate to="/account/my-products" replace />} />
          <Route path="/my-products" element={<MyProducts />} />
          <Route path="/my-products/create" element={<ProductCreate />} />
          <Route path="/my-products/edit/:id" element={<ProductEdit />} />
        </Routes>
      </div>
    </div>
  );
};

export default AccountLayout;
