import React, { useEffect, useState } from 'react';
import { Plus, Edit, Trash2 } from 'lucide-react';
import { API_BASE } from '../constants';

export default function ClassList() {
  const [classes, setClasses] = useState<any[]>([]);

  useEffect(() => {
    fetch(`${API_BASE}/class_list.php`)
      .then(res => res.json())
      .then(data => setClasses(data));
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-green-950">Class Management</h1>
          <p className="text-green-700">Manage all classes and sections.</p>
        </div>
        <button className="bg-green-950 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-green-900 transition-all shadow-lg shadow-green-950/20">
          <Plus className="w-5 h-5" />
          Add New Class
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-green-50 text-green-700 text-xs uppercase tracking-wider font-semibold">
              <th className="px-6 py-4 border-b border-green-100">#</th>
              <th className="px-6 py-4 border-b border-green-100">Curriculum</th>
              <th className="px-6 py-4 border-b border-green-100">Level</th>
              <th className="px-6 py-4 border-b border-green-100">Section</th>
              <th className="px-6 py-4 border-b border-green-100 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-green-100">
            {classes.map((c, idx) => (
              <tr key={c.id} className="hover:bg-green-50/50 transition-colors">
                <td className="px-6 py-4 text-sm text-green-700">{idx + 1}</td>
                <td className="px-6 py-4 text-sm font-medium text-green-950">{c.curriculum}</td>
                <td className="px-6 py-4 text-sm text-green-800">{c.level}</td>
                <td className="px-6 py-4 text-sm text-green-800">{c.section}</td>
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
