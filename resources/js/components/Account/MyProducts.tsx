import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { productsApi, RawProduct } from '../../api/productsApi';
import './MyProducts.css';

interface LocationState {
  message?: string;
}

const MyProducts: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [products, setProducts] = useState<RawProduct[]>([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');

  useEffect(() => {
    loadProducts();
    
    // Check for success message from location state
    const state = location.state as LocationState;
    if (state?.message) {
      setMessage(state.message);
      // Clear the message after 5 seconds
      setTimeout(() => setMessage(''), 5000);
      // Clear location state
      window.history.replaceState({}, document.title);
    }
  }, [location]);

  const loadProducts = async () => {
    try {
      setLoading(true);
      // Replace with actual user ID from auth context
      const userProducts = await productsApi.getUserProducts(1);
      setProducts(userProducts);
    } catch (error) {
      console.error('Failed to load products:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteProduct = async (productId: number) => {
    if (window.confirm('Are you sure you want to delete this product?')) {
      try {
        await productsApi.deleteProduct(productId);
        setMessage('Product deleted successfully');
        loadProducts(); // Reload the list
      } catch (error: any) {
        alert(error.message || 'Failed to delete product');
      }
    }
  };

  const formatPrice = (price: string): string => {
    const numPrice = parseFloat(price);
    if (isNaN(numPrice)) return 'UGX 0';
    return `UGX ${numPrice.toLocaleString()}`;
  };

  const getImageUrl = (product: RawProduct): string => {
    if (product.feature_photo && product.feature_photo !== 'no_image.jpg') {
      return `/storage/${product.feature_photo}`;
    }
    return '/images/no-product-image.jpg';
  };

  if (loading) {
    return (
      <div className="my-products">
        <div className="products-header">
          <h1>My Products</h1>
        </div>
        <div className="loading-state">
          <div className="spinner"></div>
          <p>Loading your products...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="my-products">
      <div className="products-header">
        <h1>My Products</h1>
        <button
          className="create-product-btn"
          onClick={() => navigate('/account/my-products/create')}
        >
          + Add New Product
        </button>
      </div>

      {message && (
        <div className="success-message">
          {message}
        </div>
      )}

      {products.length === 0 ? (
        <div className="empty-state">
          <div className="empty-icon">📦</div>
          <h2>No Products Yet</h2>
          <p>Start selling by adding your first product</p>
          <button
            className="create-first-product-btn"
            onClick={() => navigate('/account/my-products/create')}
          >
            Create Your First Product
          </button>
        </div>
      ) : (
        <div className="products-grid">
          {products.map(product => (
            <div key={product.id} className="product-card">
              <div className="product-image">
                <img
                  src={getImageUrl(product)}
                  alt={product.name}
                  onError={(e) => {
                    const target = e.target as HTMLImageElement;
                    target.src = '/images/no-product-image.jpg';
                  }}
                />
                <div className="product-status">
                  <span className={`status-badge ${product.status === '1' ? 'active' : 'inactive'}`}>
                    {product.status === '1' ? 'Active' : 'Inactive'}
                  </span>
                </div>
              </div>

              <div className="product-info">
                <h3 className="product-name">{product.name}</h3>
                <div className="product-pricing">
                  <span className="selling-price">{formatPrice(product.price_1)}</span>
                  {product.price_2 && parseFloat(product.price_2) > parseFloat(product.price_1) && (
                    <span className="original-price">{formatPrice(product.price_2)}</span>
                  )}
                </div>
                <div className="product-meta">
                  <span className="category">{product.category_text || 'Uncategorized'}</span>
                  <span className="stock-status">
                    {product.in_stock === '1' ? '✅ In Stock' : '❌ Out of Stock'}
                  </span>
                </div>
                <div className="product-contact">
                  <span className="phone">📞 {product.url}</span>
                </div>
              </div>

              <div className="product-actions">
                <button
                  className="edit-btn"
                  onClick={() => navigate(`/account/my-products/edit/${product.id}`)}
                >
                  Edit
                </button>
                <button
                  className="delete-btn"
                  onClick={() => handleDeleteProduct(product.id)}
                >
                  Delete
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default MyProducts;
