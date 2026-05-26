import React, { useEffect, useState } from 'react';
import { Plus, Edit, Trash2 } from 'lucide-react';
import { API_BASE } from '../constants';

export default function SubjectList() {
  const [subjects, setSubjects] = useState<any[]>([]);

  useEffect(() => {
    fetch(`${API_BASE}/subject_list.php`)
      .then(res => res.json())
      .then(data => setSubjects(data));
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-green-950">Subject Management</h1>
          <p className="text-green-700">Manage all subjects and courses.</p>
        </div>
        <button className="bg-green-950 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-green-900 transition-all shadow-lg shadow-green-950/20">
          <Plus className="w-5 h-5" />
          Add New Subject
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-green-50 text-green-700 text-xs uppercase tracking-wider font-semibold">
              <th className="px-6 py-4 border-b border-green-100">#</th>
              <th className="px-6 py-4 border-b border-green-100">Code</th>
              <th className="px-6 py-4 border-b border-green-100">Subject</th>
              <th className="px-6 py-4 border-b border-green-100">Description</th>
              <th className="px-6 py-4 border-b border-green-100 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-green-100">
            {subjects.map((s, idx) => (
              <tr key={s.id} className="hover:bg-green-50/50 transition-colors">
                <td className="px-6 py-4 text-sm text-green-700">{idx + 1}</td>
                <td className="px-6 py-4 text-sm font-mono font-bold text-green-950">{s.code}</td>
                <td className="px-6 py-4 text-sm font-medium text-green-900">{s.subject}</td>
                <td className="px-6 py-4 text-sm text-green-700 max-w-xs truncate">{s.description}</td>
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
