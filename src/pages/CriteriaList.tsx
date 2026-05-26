import React, { useEffect, useState } from 'react';
import { Plus, Edit, Trash2, GripVertical } from 'lucide-react';
import { API_BASE } from '../constants';

export default function CriteriaList() {
  const [criteria, setCriteria] = useState<any[]>([]);

  useEffect(() => {
    fetch(`${API_BASE}/criteria_list.php`)
      .then(res => res.json())
      .then(data => setCriteria(data));
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-green-950">Evaluation Criteria</h1>
          <p className="text-green-700">Define the categories for faculty evaluation.</p>
        </div>
        <button className="bg-green-950 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-green-900 transition-all shadow-lg shadow-green-950/20">
          <Plus className="w-5 h-5" />
          Add New Criteria
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-green-50 text-green-700 text-xs uppercase tracking-wider font-semibold">
              <th className="px-6 py-4 border-b border-green-100 w-12">#</th>
              <th className="px-6 py-4 border-b border-green-100">Criteria Name</th>
              <th className="px-6 py-4 border-b border-green-100 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-green-100">
            {criteria.map((c, idx) => (
              <tr key={c.id} className="hover:bg-green-50/50 transition-colors group">
                <td className="px-6 py-4 text-sm text-green-700 flex items-center gap-2">
                  <GripVertical className="w-4 h-4 text-green-300 group-hover:text-green-400 cursor-grab" />
                  {idx + 1}
                </td>
                <td className="px-6 py-4 text-sm font-medium text-green-950">{c.criteria}</td>
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
