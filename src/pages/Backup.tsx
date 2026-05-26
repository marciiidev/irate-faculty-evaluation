import React from 'react';
import { useAuthStore } from '../store';
import { Database, Download, RefreshCw, Trash2 } from 'lucide-react';
import Swal from 'sweetalert2';

export default function Backup() {
  const handleBackup = () => {
    Swal.fire({
      title: 'Generating Backup...',
      html: 'Please wait while we prepare your system backup.',
      timer: 2000,
      timerProgressBar: true,
      didOpen: () => Swal.showLoading(),
    }).then(() => {
      Swal.fire('Success', 'Backup generated and downloaded successfully.', 'success');
    });
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-green-950">System Backup & Recovery</h1>
        <p className="text-green-700">Manage system data, backups, and retrieve deleted records.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-sm space-y-6">
          <div className="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
            <Database className="w-6 h-6" />
          </div>
          <div>
            <h3 className="text-xl font-bold text-green-950">Full System Backup</h3>
            <p className="text-green-700 text-sm mt-1">Generate a complete snapshot of the database including all users, evaluations, and configurations.</p>
          </div>
          <button 
            onClick={handleBackup}
            className="w-full bg-green-950 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-green-900 transition-all"
          >
            <Download className="w-5 h-5" />
            Download SQL Backup
          </button>
        </div>

        <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-sm space-y-6">
          <div className="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
            <RefreshCw className="w-6 h-6" />
          </div>
          <div>
            <h3 className="text-xl font-bold text-green-950">Data Recovery</h3>
            <p className="text-green-700 text-sm mt-1">View and restore recently deleted records from the system trash.</p>
          </div>
          <button className="w-full bg-green-600 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-green-700 transition-all">
            <RefreshCw className="w-5 h-5" />
            Open Recovery Console
          </button>
        </div>
      </div>

      <div className="bg-white rounded-3xl border border-green-200 shadow-sm overflow-hidden">
        <div className="p-6 border-b border-green-100 bg-green-50/50 flex items-center justify-between">
          <h3 className="font-bold text-green-900">Recent Deletions</h3>
          <span className="text-xs font-bold text-green-400 uppercase tracking-widest">Last 30 Days</span>
        </div>
        <div className="p-12 text-center">
          <Trash2 className="w-12 h-12 text-green-200 mx-auto mb-4" />
          <p className="text-green-700 italic">No deleted records found in the recovery bin.</p>
        </div>
      </div>
    </div>
  );
}
