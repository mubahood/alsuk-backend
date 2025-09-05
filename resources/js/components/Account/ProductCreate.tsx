import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDropzone } from 'react-dropzone';
import { productsApi, ProductFormData } from '../../api/productsApi';
import { categoriesApi } from '../../api/categoriesApi';
import './ProductCreate.css';

interface Category {
  id: number;
  category: string;
  attributes?: string;
}

interface DropZoneProps {
  onFilesAdded: (files: File[]) => void;
  files: File[];
  maxFiles?: number;
}

const PhotoDropZone: React.FC<DropZoneProps> = ({ onFilesAdded, files, maxFiles = 10 }) => {
  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    accept: {
      'image/*': ['.jpeg', '.jpg', '.png', '.gif', '.webp']
    },
    maxFiles: maxFiles,
    onDrop: (acceptedFiles) => {
      onFilesAdded([...files, ...acceptedFiles]);
    }
  });

  const removeFile = (index: number) => {
    const newFiles = files.filter((_, i) => i !== index);
    onFilesAdded(newFiles);
  };

  return (
    <div className="photo-upload-section">
      <div {...getRootProps()} className={`dropzone ${isDragActive ? 'dropzone-active' : ''}`}>
        <input {...getInputProps()} />
        <div className="dropzone-content">
          <div className="camera-icon">📷</div>
          <p className="dropzone-text">
            {isDragActive
              ? "Drop photos here..."
              : "Drop photos here or click to select"}
          </p>
          <p className="dropzone-hint">
            Add up to {maxFiles} product photos (JPG, PNG, GIF, WebP)
          </p>
        </div>
      </div>

      {files.length > 0 && (
        <div className="uploaded-photos">
          <h4>Uploaded Photos ({files.length})</h4>
          <div className="photos-grid">
            {files.map((file, index) => (
              <div key={index} className="photo-preview">
                <img
                  src={URL.createObjectURL(file)}
                  alt={`Preview ${index + 1}`}
                  className="preview-image"
                />
                <button
                  type="button"
                  className="remove-photo"
                  onClick={() => removeFile(index)}
                >
                  ×
                </button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

const ProductCreate: React.FC = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState<Category[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);
  const [photos, setPhotos] = useState<File[]>([]);
  const [formData, setFormData] = useState<ProductFormData>({
    name: '',
    url: '', // Contact phone
    supplier: '', // Address
    category: '',
    price_1: '', // Selling price
    price_2: '', // Original price
    description: '',
    local_id: Date.now().toString(),
    has_colors: 'No',
    has_sizes: 'No',
    colors: '',
    sizes: '',
    p_type: 'No'
  });
  const [errors, setErrors] = useState<{ [key: string]: string }>({});

  useEffect(() => {
    loadCategories();
  }, []);

  const loadCategories = async () => {
    try {
      const response = await categoriesApi.getCategories();
      setCategories(response);
    } catch (error) {
      console.error('Failed to load categories:', error);
    }
  };

  const validateForm = (): boolean => {
    const newErrors: { [key: string]: string } = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Product name is required';
    }

    if (!formData.url.trim()) {
      newErrors.url = 'Contact phone number is required';
    } else if (formData.url.length < 5) {
      newErrors.url = 'Phone number should be at least 5 digits';
    }

    if (!formData.supplier.trim()) {
      newErrors.supplier = 'Product/Service address is required';
    } else if (formData.supplier.length < 5) {
      newErrors.supplier = 'Address should be at least 5 characters';
    }

    if (!selectedCategory) {
      newErrors.category = 'Please select a category';
    }

    if (!formData.price_1 || parseFloat(formData.price_1) < 1) {
      newErrors.price_1 = 'Selling price must be at least 1';
    }

    if (!formData.price_2 || parseFloat(formData.price_2) < 1) {
      newErrors.price_2 = 'Original price must be at least 1';
    }

    if (parseFloat(formData.price_2) < parseFloat(formData.price_1)) {
      newErrors.price_2 = 'Original price should be greater than selling price';
    }

    if (photos.length === 0) {
      newErrors.photos = 'Please add at least one product photo';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleInputChange = (field: keyof ProductFormData, value: string) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));

    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: ''
      }));
    }
  };

  const handleCategorySelect = (category: Category) => {
    setSelectedCategory(category);
    setFormData(prev => ({
      ...prev,
      category: category.id.toString()
    }));

    if (errors.category) {
      setErrors(prev => ({
        ...prev,
        category: ''
      }));
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setLoading(true);

    try {
      // Create form data with file uploads
      const submitData = new FormData();
      
      Object.entries(formData).forEach(([key, value]) => {
        submitData.append(key, value);
      });

      // Add photos
      photos.forEach((photo, index) => {
        submitData.append(`photos[${index}]`, photo);
      });

      // Add user ID (should come from auth context)
      submitData.append('user', '1'); // Replace with actual user ID

      const response = await productsApi.createProduct(submitData);
      
      if (response.success) {
        navigate('/account/my-products', { 
          state: { message: 'Product created successfully!' }
        });
      }
    } catch (error: any) {
      console.error('Failed to create product:', error);
      setErrors({
        submit: error.message || 'Failed to create product. Please try again.'
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="product-create">
      <div className="product-create-header">
        <button 
          type="button" 
          className="back-button"
          onClick={() => navigate('/account/my-products')}
        >
          ← Back
        </button>
        <h1>Create New Product</h1>
      </div>

      <form onSubmit={handleSubmit} className="product-form">
        {/* Photo Upload Section */}
        <div className="form-section">
          <PhotoDropZone
            onFilesAdded={setPhotos}
            files={photos}
            maxFiles={10}
          />
          {errors.photos && <span className="error-text">{errors.photos}</span>}
        </div>

        {/* Basic Information */}
        <div className="form-section">
          <h3>Basic Information</h3>
          
          <div className="form-group">
            <label htmlFor="name">Product Name *</label>
            <input
              type="text"
              id="name"
              value={formData.name}
              onChange={(e) => handleInputChange('name', e.target.value)}
              className={errors.name ? 'error' : ''}
              placeholder="Enter product name"
            />
            {errors.name && <span className="error-text">{errors.name}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="url">Contact Phone Number *</label>
            <input
              type="tel"
              id="url"
              value={formData.url}
              onChange={(e) => handleInputChange('url', e.target.value)}
              className={errors.url ? 'error' : ''}
              placeholder="Enter contact phone number"
            />
            {errors.url && <span className="error-text">{errors.url}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="supplier">Product/Service Address *</label>
            <input
              type="text"
              id="supplier"
              value={formData.supplier}
              onChange={(e) => handleInputChange('supplier', e.target.value)}
              className={errors.supplier ? 'error' : ''}
              placeholder="Enter product location/address"
            />
            {errors.supplier && <span className="error-text">{errors.supplier}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="category">Product Category *</label>
            <div className="category-selector">
              <input
                type="text"
                id="category"
                value={selectedCategory?.category || ''}
                readOnly
                className={errors.category ? 'error' : ''}
                placeholder="Select category"
                onClick={() => document.getElementById('category-dropdown')?.classList.toggle('show')}
              />
              <div id="category-dropdown" className="category-dropdown">
                {categories.map(category => (
                  <div
                    key={category.id}
                    className="category-option"
                    onClick={() => handleCategorySelect(category)}
                  >
                    {category.category}
                  </div>
                ))}
              </div>
            </div>
            {errors.category && <span className="error-text">{errors.category}</span>}
          </div>
        </div>

        {/* Pricing Information */}
        <div className="form-section">
          <h3>Pricing</h3>
          
          <div className="form-row">
            <div className="form-group">
              <label htmlFor="price_2">Original Price (UGX) *</label>
              <input
                type="number"
                id="price_2"
                value={formData.price_2}
                onChange={(e) => handleInputChange('price_2', e.target.value)}
                className={errors.price_2 ? 'error' : ''}
                placeholder="Enter original price"
                min="1"
              />
              {errors.price_2 && <span className="error-text">{errors.price_2}</span>}
            </div>

            <div className="form-group">
              <label htmlFor="price_1">Selling Price (UGX) *</label>
              <input
                type="number"
                id="price_1"
                value={formData.price_1}
                onChange={(e) => handleInputChange('price_1', e.target.value)}
                className={errors.price_1 ? 'error' : ''}
                placeholder="Enter selling price"
                min="1"
              />
              {errors.price_1 && <span className="error-text">{errors.price_1}</span>}
            </div>
          </div>
        </div>

        {/* Description */}
        <div className="form-section">
          <h3>Product Description</h3>
          
          <div className="form-group">
            <label htmlFor="description">Description</label>
            <textarea
              id="description"
              value={formData.description}
              onChange={(e) => handleInputChange('description', e.target.value)}
              placeholder="Describe your product or service..."
              rows={6}
            />
          </div>
        </div>

        {/* Error Display */}
        {errors.submit && (
          <div className="form-section">
            <div className="error-message">
              {errors.submit}
            </div>
          </div>
        )}

        {/* Submit Button */}
        <div className="form-actions">
          <button
            type="submit"
            className="submit-button"
            disabled={loading}
          >
            {loading ? 'Creating Product...' : 'Create Product'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ProductCreate;
