import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import Button from '../components/Button';
import Input from '../components/Input';
import Alert from '../components/Alert';
import { authAPI } from '../services/apiClient';
import { Mail, ArrowLeft } from 'lucide-react';

const ForgotPasswordPage: React.FC = () => {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      await authAPI.forgotPassword(email);
      setSuccess(true);
    } catch (err: any) {
      setError(err.message || 'Failed to send reset email. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="flex items-center justify-center min-h-[calc(100vh-200px)] py-12">
        <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md text-center">
          <div className="mb-6">
            <div className="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
              <Mail className="h-8 w-8 text-green-600" />
            </div>
          </div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Check Your Email</h2>
          <p className="text-gray-600 mb-6">
            We've sent a password reset link to <strong>{email}</strong>. 
            Please check your inbox and follow the instructions.
          </p>
          <p className="text-sm text-gray-500 mb-6">
            Didn't receive the email? Check your spam folder or try again in a few minutes.
          </p>
          <div className="space-y-3">
            <Button onClick={() => navigate('/auth')} className="w-full">
              Back to Login
            </Button>
            <button
              onClick={() => {
                setSuccess(false);
                setEmail('');
              }}
              className="text-sm text-green-600 hover:text-green-500 font-medium"
            >
              Try different email
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center min-h-[calc(100vh-200px)] py-12">
      <div className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <Link 
          to="/auth" 
          className="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 mb-6"
        >
          <ArrowLeft className="h-4 w-4 mr-1" />
          Back to Login
        </Link>

        <h2 className="text-3xl font-bold text-gray-800 mb-2">Forgot Password?</h2>
        <p className="text-gray-600 mb-8">
          No worries! Enter your email address and we'll send you a link to reset your password.
        </p>

        {error && <Alert type="error" message={error} onClose={() => setError(null)} />}

        <form onSubmit={handleSubmit} className="space-y-6">
          <Input
            label="Email Address"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="you@example.com"
            required
          />

          <Button type="submit" isLoading={loading} className="w-full !py-3 !text-base">
            Send Reset Link
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

export default ForgotPasswordPage;
