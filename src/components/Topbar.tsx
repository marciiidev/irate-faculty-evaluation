import React from 'react';
import { useAuthStore } from '../store';
import { User, Settings, LogOut, Menu } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function Topbar() {
  const { user, type, toggleSidebar } = useAuthStore();

  return (
    <header className="h-16 bg-white border-b border-green-200 flex items-center justify-between px-8 sticky top-0 z-10">
      <div className="flex items-center gap-4">
        <button 
          onClick={toggleSidebar}
          className="lg:hidden bg-green-700 text-white p-2 rounded-lg hover:bg-green-800 transition-all shadow-md"
        >
          <Menu className="w-5 h-5" />
        </button>
        <h2 className="text-green-600 font-medium capitalize">{type} Dashboard</h2>
      </div>
      <div className="flex items-center gap-4">
        <Link to="/settings.php" className="p-2 hover:bg-green-100 rounded-full text-green-600 transition-all">
          <Settings className="w-5 h-5" />
        </Link>
        <div className="flex items-center gap-3">
          <div className="text-right hidden sm:block">
            <p className="text-sm font-semibold text-green-900">{user?.firstname} {user?.lastname}</p>
            <p className="text-xs text-green-600 capitalize">{user?.role || 'User'}</p>
          </div>
          <div className="w-10 h-10 rounded-full bg-green-100 border border-green-200 flex items-center justify-center overflow-hidden">
            {user?.avatar ? (
              <img src={user.avatar} alt="Avatar" className="w-full h-full object-cover" />
            ) : (
              <User className="w-6 h-6 text-green-400" />
            )}
          </div>
        </div>
      </div>
    </header>
  );
}
