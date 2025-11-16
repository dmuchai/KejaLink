import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { APP_NAME, NavigationLinks, UserCircleIcon } from '../constants';
import { useAuth } from '../hooks/useAuth';

const Navbar: React.FC = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isProductsOpenMobile, setIsProductsOpenMobile] = useState(false);
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  const handleListProperty = () => {
    if (user && user.role === 'agent') {
      navigate('/dashboard/agent');
    } else {
      navigate('/auth?mode=register&role=agent&returnTo=/dashboard/agent');
    }
  };

  // Build navigation links dynamically: hide Agent Dashboard until an agent is logged in
  const computedLinks = NavigationLinks.filter(l => l.name !== 'Agent Dashboard');
  if (user && user.role === 'agent') {
    computedLinks.push({ name: 'Agent Dashboard', path: '/dashboard/agent' });
  }

  return (
    <nav className="bg-white shadow-md sticky top-0 z-50">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          <Link to="/" className="flex items-center space-x-2 text-2xl font-bold text-green-600 hover:text-green-700 transition-colors">
            <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
            <span>{APP_NAME}</span>
          </Link>
          
          {/* Desktop Menu */}
          <div className="hidden md:flex items-center space-x-6">
            {computedLinks.map((link) => (
              <Link
                key={link.name}
                to={link.path}
                className="text-gray-700 hover:text-green-600 transition-colors"
              >
                {link.name}
              </Link>
            ))}
            {/* Products dropdown */}
            <div className="relative group">
              <button className="flex items-center gap-1 text-gray-700 hover:text-green-600 transition-colors" aria-haspopup="menu" aria-expanded="false">
                <span>Products</span>
                <svg
                  className="w-4 h-4"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  aria-hidden="true"
                >
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </button>
              <div className="absolute left-0 mt-2 w-72 bg-white rounded-md shadow-lg py-2 hidden group-hover:block">
                <a
                  href="https://moving-planner-ke.vercel.app/"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50"
                >
                  Movelink — Professional Movers
                </a>
                <a
                  href="https://property-manager-ke.vercel.app/"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50"
                >
                  KejaLink Property Management System
                </a>
              </div>
            </div>
            <button
              onClick={handleListProperty}
              className="px-3 py-2 border border-green-600 text-green-700 rounded-md hover:bg-green-50 transition-colors flex items-center gap-2"
              aria-label="List a Property"
            >
              <svg
                className="w-4 h-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <path d="M12 5v14M5 12h14" />
              </svg>
              <span>List a Property</span>
            </button>
            {user ? (
              <div className="relative group">
                <button className="flex items-center text-gray-700 hover:text-green-600">
                  <UserCircleIcon className="w-6 h-6 mr-1" />
                  <span>{user.full_name.split(' ')[0]}</span> 
                </button>
                <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                  {user.role === 'agent' && (
                     <Link to="/dashboard/agent" className="block px-4 py-2 text-sm text-gray-700 hover:bg-green-100">My Dashboard</Link>
                  )}
                  <button onClick={handleLogout} className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-100">
                    Logout
                  </button>
                </div>
              </div>
            ) : (
              <Link to="/auth?mode=login" className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                Login / Register
              </Link>
            )}
          </div>

          {/* Mobile Menu Button */}
          <div className="md:hidden">
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="text-gray-700 hover:text-green-600 focus:outline-none"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={isMobileMenuOpen ? "M6 18L18 6M6 6l12 12" : "M4 6h16M4 12h16M4 18h16"}></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-white shadow-lg absolute w-full">
          <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            {computedLinks.map((link) => (
              <Link
                key={link.name}
                to={link.path}
                onClick={() => setIsMobileMenuOpen(false)}
                className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-green-50"
              >
                {link.name}
              </Link>
            ))}
            {/* Products collapsible */}
            <button
              onClick={() => setIsProductsOpenMobile((v) => !v)}
              className="w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-green-50"
              aria-expanded={isProductsOpenMobile}
              aria-controls="mobile-products-menu"
            >
              <span>Products</span>
              <svg
                className={`w-4 h-4 transition-transform ${isProductsOpenMobile ? 'rotate-180' : ''}`}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <path d="M6 9l6 6 6-6" />
              </svg>
            </button>
            {isProductsOpenMobile && (
              <div id="mobile-products-menu" className="ml-3 space-y-1">
                <a
                  href="https://moving-planner-ke.vercel.app/"
                  target="_blank"
                  rel="noopener noreferrer"
                  onClick={() => { setIsMobileMenuOpen(false); setIsProductsOpenMobile(false); }}
                  className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-green-50"
                >
                  Movelink — Professional Movers
                </a>
                <a
                  href="https://property-manager-ke.vercel.app/"
                  target="_blank"
                  rel="noopener noreferrer"
                  onClick={() => { setIsMobileMenuOpen(false); setIsProductsOpenMobile(false); }}
                  className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-green-50"
                >
                  KejaLink Property Management System
                </a>
              </div>
            )}
            <button
              onClick={() => { handleListProperty(); setIsMobileMenuOpen(false); }}
              className="block w-full text-left px-3 py-2 rounded-md text-base font-medium border border-green-600 text-green-700 bg-white hover:bg-green-50"
              aria-label="List a Property"
            >
              <span className="inline-flex items-center gap-2">
                <svg
                  className="w-4 h-4"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  aria-hidden="true"
                >
                  <path d="M12 5v14M5 12h14" />
                </svg>
                List a Property
              </span>
            </button>
            {user ? (
              <>
                {user.role === 'agent' && (
                  <Link to="/dashboard/agent" onClick={() => setIsMobileMenuOpen(false)} className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-green-50">My Dashboard</Link>
                )}
                <button onClick={() => { handleLogout(); setIsMobileMenuOpen(false); }} className="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">
                  Logout ({user.full_name.split(' ')[0]})
                </button>
              </>
            ) : (
              <Link
                to="/auth?mode=login"
                onClick={() => setIsMobileMenuOpen(false)}
                className="block px-3 py-2 rounded-md text-base font-medium text-white bg-green-600 hover:bg-green-700"
              >
                Login / Register
              </Link>
            )}
          </div>
        </div>
      )}
    </nav>
  );
};

export default Navbar;
