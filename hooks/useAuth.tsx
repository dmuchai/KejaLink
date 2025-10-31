import React, { createContext, useContext, useEffect, useState } from 'react';
import { authAPI, storage, User } from '../services/apiClient';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  error: string | null;
  login: (email: string, password: string) => Promise<void>;
  register: (email: string, password: string, name: string, role: string) => Promise<void>;
  logout: () => Promise<void>;
  clearError: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Check for existing session on mount
  useEffect(() => {
    const initAuth = async () => {
      try {
        const token = storage.getToken();
        if (token) {
          // Verify token is still valid by fetching profile
          const { user: profile } = await authAPI.getProfile();
          setUser(profile);
        }
      } catch (err) {
        // Token invalid or expired, clear storage
        storage.clear();
        setUser(null);
      } finally {
        setLoading(false);
      }
    };

    initAuth();
  }, []);

  const login = async (email: string, password: string) => {
    setLoading(true);
    setError(null);

    try {
      const response = await authAPI.login({ email, password });
      
      // Store token and user
      storage.setToken(response.token);
      storage.setUser(response.user);
      setUser(response.user);
    } catch (err: any) {
      const errorMessage = err.message || 'Login failed';
      console.error('Login error:', errorMessage);
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const register = async (email: string, password: string, name: string, role: string) => {
    console.log("Registering:", { email, password, name, role });
    setLoading(true);
    setError(null);

    try {
      const response = await authAPI.register({
        email,
        password,
        full_name: name,
        role: role as 'tenant' | 'agent',
      });

      // Store token and user
      storage.setToken(response.token);
      storage.setUser(response.user);
      setUser(response.user);
    } catch (err: any) {
      const errorMessage = err.message || 'Registration failed';
      console.error('Registration error:', errorMessage);
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    setLoading(true);
    setError(null);

    try {
      await authAPI.logout();
      storage.clear();
      setUser(null);
    } catch (err: any) {
      const errorMessage = err.message || 'Logout failed';
      console.error('Logout error:', errorMessage);
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const clearError = () => {
    setError(null);
  };

  return (
    <AuthContext.Provider value={{ user, loading, error, login, register, logout, clearError }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used within an AuthProvider');
  return context;
};
