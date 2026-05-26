import React, { useState } from 'react';
import { useAuthStore } from '../store';
import { User, Mail, Lock, Camera, Save } from 'lucide-react';
import Swal from 'sweetalert2';

export default function ManageAccount() {
  const { user } = useAuthStore();
  const [formData, setFormData] = useState({
    firstname: user?.firstname || '',
    lastname: user?.lastname || '',
    email: user?.email || '',
    password: '',
    confirmPassword: ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (formData.password && formData.password !== formData.confirmPassword) {
      Swal.fire('Error', 'Passwords do not match', 'error');
      return;
    }
    Swal.fire('Success', 'Profile updated successfully', 'success');
  };

  return (
    <div className="max-w-2xl mx-auto space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-green-950">Manage Account</h1>
        <p className="text-green-700">Update your personal information and security settings.</p>
      </div>

      <div className="bg-white rounded-3xl border border-green-200 shadow-sm overflow-hidden">
        <div className="p-8 bg-green-50 border-b border-green-100 flex flex-col items-center">
          <div className="relative group">
            <div className="w-32 h-32 rounded-full bg-white border-4 border-white shadow-xl overflow-hidden">
              {user?.avatar ? (
                <img src={user.avatar} className="w-full h-full object-cover" />
              ) : (
                <div className="w-full h-full bg-green-100 flex items-center justify-center">
                  <User className="w-16 h-16 text-green-300" />
                </div>
              )}
            </div>
            <button className="absolute bottom-0 right-0 p-2 bg-green-950 text-white rounded-full shadow-lg hover:bg-green-900 transition-all">
              <Camera className="w-5 h-5" />
            </button>
          </div>
          <h2 className="mt-4 text-xl font-bold text-green-950">{user?.firstname} {user?.lastname}</h2>
          <p className="text-green-700 text-sm capitalize">{user?.role}</p>
        </div>

        <form onSubmit={handleSubmit} className="p-8 space-y-6">
          <div className="grid grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-sm font-bold text-green-800">First Name</label>
              <div className="relative">
                <User className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-green-400" />
                <input 
                  type="text" 
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none"
                  value={formData.firstname}
                  onChange={(e) => setFormData({...formData, firstname: e.target.value})}
                />
              </div>
            </div>
            <div className="space-y-2">
              <label className="text-sm font-bold text-green-800">Last Name</label>
              <div className="relative">
                <User className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-green-400" />
                <input 
                  type="text" 
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none"
                  value={formData.lastname}
                  onChange={(e) => setFormData({...formData, lastname: e.target.value})}
                />
              </div>
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-bold text-green-800">Email Address</label>
            <div className="relative">
              <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-green-400" />
              <input 
                type="email" 
                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none"
                value={formData.email}
                onChange={(e) => setFormData({...formData, email: e.target.value})}
              />
            </div>
          </div>

          <div className="pt-6 border-t border-green-100 space-y-6">
            <div className="space-y-2">
              <label className="text-sm font-bold text-green-800">New Password</label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-green-400" />
                <input 
                  type="password" 
                  placeholder="Leave blank to keep current"
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none"
                  value={formData.password}
                  onChange={(e) => setFormData({...formData, password: e.target.value})}
                />
              </div>
            </div>
            <div className="space-y-2">
              <label className="text-sm font-bold text-green-800">Confirm New Password</label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-green-400" />
                <input 
                  type="password" 
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none"
                  value={formData.confirmPassword}
                  onChange={(e) => setFormData({...formData, confirmPassword: e.target.value})}
                />
              </div>
            </div>
          </div>

          <button 
            type="submit"
            className="w-full bg-green-950 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-green-900 transition-all shadow-lg"
          >
            <Save className="w-5 h-5" />
            Save Changes
          </button>
        </form>
      </div>
    </div>
  );
}
