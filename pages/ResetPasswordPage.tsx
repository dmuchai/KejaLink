import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import Button from '../components/Button';
import Input from '../components/Input';
import Alert from '../components/Alert';
import { authAPI } from '../services/apiClient';
import { CheckCircle, XCircle } from 'lucide-react';

const ResetPasswordPage: React.FC = () => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token');

  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const [validatingToken, setValidatingToken] = useState(true);
  const [tokenValid, setTokenValid] = useState(false);

  // Validate token on mount
  useEffect(() => {
    const validateToken = async () => {
      if (!token) {
        setError('Invalid or missing reset token');
        setValidatingToken(false);
        return;
      }

      try {
        await authAPI.validateResetToken(token);
        setTokenValid(true);
      } catch (err: any) {
        setError(err.message || 'Invalid or expired reset link');
        setTokenValid(false);
      } finally {
        setValidatingToken(false);
      }
    };

    validateToken();
  }, [token]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);

    // Validate passwords match
    if (password !== confirmPassword) {
      setError('Passwords do not match');
      return;
    }

    // Validate password length
    if (password.length < 6) {
      setError('Password must be at least 6 characters');
      return;
    }

    if (!token) {
      setError('Invalid reset token');
      return;
    }

    setLoading(true);

    try {
      await authAPI.resetPassword(token, password);
      setSuccess(true);
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        navigate('/auth');
      }, 3000);
    } catch (err: any) {
      setError(err.message || 'Failed to reset password. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  // Loading state
  if (validatingToken) {
    return (
      <div className="flex items-center justify-center min-h-[calc(100vh-200px)] py-12">
        <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Validating reset link...</p>
        </div>
      </div>
    );
  }

  // Invalid token state
  if (!tokenValid) {
    return (
      <div className="flex items-center justify-center min-h-[calc(100vh-200px)] py-12">
        <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md text-center">
          <div className="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
            <XCircle className="h-8 w-8 text-red-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Invalid Reset Link</h2>
          <p className="text-gray-600 mb-6">
            {error || 'This password reset link is invalid or has expired.'}
          </p>
          <div className="space-y-3">
            <Button onClick={() => navigate('/forgot-password')} className="w-full">
              Request New Link
            </Button>
            <Link 
              to="/auth" 
              className="block text-sm text-green-600 hover:text-green-500 font-medium"
            >
              Back to Login
            </Link>
          </div>
        </div>
      </div>
    );
  }

  // Success state
  if (success) {
    return (
      <div className="flex items-center justify-center min-h-[calc(100vh-200px)] py-12">
        <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md text-center">
          <div className="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
            <CheckCircle className="h-8 w-8 text-green-600" />
          </div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Password Reset Successful!</h2>
          <p className="text-gray-600 mb-6">
            Your password has been reset successfully. You can now log in with your new password.
          </p>
          <p className="text-sm text-gray-500 mb-6">
            Redirecting to login page...
          </p>
          <Button onClick={() => navigate('/auth')} className="w-full">
            Go to Login
          </Button>
        </div>
      </div>
    );
  }

  // Reset form
  return (
    <div className="flex items-center justify-center min-h-[calc(100vh-200px)] py-12">
      <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <h2 className="text-3xl font-bold text-gray-800 mb-2">Reset Password</h2>
        <p className="text-gray-600 mb-8">
          Enter your new password below. Make sure it's at least 6 characters long.
        </p>

        {error && <Alert type="error" message={error} onClose={() => setError(null)} />}

        <form onSubmit={handleSubmit} className="space-y-6">
          <Input
            label="New Password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="••••••••"
            required
            showPasswordToggle={true}
          />

          <Input
            label="Confirm New Password"
            type="password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            placeholder="••••••••"
            required
            showPasswordToggle={true}
          />

          <div className="bg-gray-50 border border-gray-200 rounded-md p-3">
            <p className="text-xs text-gray-600">
              <strong>Password requirements:</strong>
            </p>
            <ul className="text-xs text-gray-600 mt-1 space-y-1 list-disc list-inside">
              <li>At least 6 characters long</li>
              <li>Should include letters and numbers</li>
              <li>Avoid common passwords</li>
            </ul>
          </div>

          <Button type="submit" isLoading={loading} className="w-full !py-3 !text-base">
            Reset Password
          </Button>
        </form>

        <p className="text-center text-sm text-gray-600 mt-6">
          Remember your password?{' '}
          <Link to="/auth" className="font-medium text-green-600 hover:text-green-500">
            Login here
          </Link>
        </p>
      </div>
    </div>
  );
};

export default ResetPasswordPage;
