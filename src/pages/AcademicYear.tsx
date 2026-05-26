import React, { useEffect, useState } from 'react';
import { Plus, Edit, Trash2, CheckCircle2, XCircle } from 'lucide-react';
import Swal from 'sweetalert2';
import { API_BASE } from '../constants';

export default function AcademicYear() {
  const [academics, setAcademics] = useState<any[]>([]);

  useEffect(() => {
    fetch(`${API_BASE}/academic_list.php`)
      .then(res => res.json())
      .then(data => setAcademics(data));
  }, []);

  const getStatusBadge = (status: number) => {
    switch(status) {
      case 1: return <span className="px-2 py-1 bg-green-100 text-green-800 rounded-full text-[10px] font-bold uppercase">Ongoing</span>;
      case 2: return <span className="px-2 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase">Closed</span>;
      default: return <span className="px-2 py-1 bg-green-50 text-green-700 rounded-full text-[10px] font-bold uppercase">Pending</span>;
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-green-950">Academic Year</h1>
          <p className="text-green-700">Manage school years and evaluation periods.</p>
        </div>
        <button className="bg-green-950 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-green-900 transition-all shadow-lg shadow-green-950/20">
          <Plus className="w-5 h-5" />
          Add New Year
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-green-50 text-green-700 text-xs uppercase tracking-wider font-semibold">
              <th className="px-6 py-4 border-b border-green-100">#</th>
              <th className="px-6 py-4 border-b border-green-100">Year</th>
              <th className="px-6 py-4 border-b border-green-100">Semester</th>
              <th className="px-6 py-4 border-b border-green-100">Default</th>
              <th className="px-6 py-4 border-b border-green-100">Status</th>
              <th className="px-6 py-4 border-b border-green-100 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-green-100">
            {academics.map((ac, idx) => (
              <tr key={ac.id} className="hover:bg-green-50/50 transition-colors">
                <td className="px-6 py-4 text-sm text-green-700">{idx + 1}</td>
                <td className="px-6 py-4 text-sm font-medium text-green-950">{ac.year}</td>
                <td className="px-6 py-4 text-sm text-green-700">{ac.semester === 1 ? '1st' : '2nd'} Semester</td>
                <td className="px-6 py-4">
                  {ac.is_default ? <CheckCircle2 className="w-5 h-5 text-green-600" /> : <XCircle className="w-5 h-5 text-green-200" />}
                </td>
                <td className="px-6 py-4">{getStatusBadge(ac.status)}</td>
                <td className="px-6 py-4 text-right">
                  <div className="flex items-center justify-end gap-2">
                    <button className="p-2 hover:bg-green-50 rounded-lg text-green-400 hover:text-green-600 transition-all"><Edit className="w-4 h-4" /></button>
                    <button className="p-2 hover:bg-red-50 rounded-lg text-green-400 hover:text-red-600 transition-all"><Trash2 className="w-4 h-4" /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
