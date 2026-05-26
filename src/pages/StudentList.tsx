import React, { useEffect, useState } from 'react';
import { Plus, Search, Edit, Trash2, Eye } from 'lucide-react';
import Swal from 'sweetalert2';
import { API_BASE } from '../constants';

export default function StudentList() {
  const [students, setStudents] = useState<any[]>([]);
  const [classes, setClasses] = useState<any[]>([]);
  const [search, setSearch] = useState('');

  useEffect(() => {
    Promise.all([
      fetch(`${API_BASE}/student_list.php`).then(res => res.json()),
      fetch(`${API_BASE}/class_list.php`).then(res => res.json())
    ]).then(([s, c]) => {
      setStudents(s);
      setClasses(c);
    });
  }, []);

  const getClassName = (id: number) => {
    const c = classes.find(cl => cl.id === id);
    return c ? `${c.curriculum} ${c.level}-${c.section}` : 'N/A';
  };

  const filtered = students.filter(s => 
    `${s.firstname} ${s.lastname}`.toLowerCase().includes(search.toLowerCase()) ||
    s.school_id?.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-green-950">Student Management</h1>
          <p className="text-green-700">Manage all students and their class assignments.</p>
        </div>
        <button className="bg-green-950 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-green-900 transition-all shadow-lg shadow-green-950/20">
          <Plus className="w-5 h-5" />
          Add New Student
        </button>
      </div>

      <div className="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
        <div className="p-4 border-b border-green-100 flex items-center justify-between bg-green-50/50">
          <div className="relative w-72">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-green-400 w-4 h-4" />
            <input
              type="text"
              placeholder="Search students..."
              className="w-full pl-10 pr-4 py-2 rounded-lg border border-green-200 focus:ring-2 focus:ring-green-950 outline-none text-sm"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-green-50 text-green-700 text-xs uppercase tracking-wider font-semibold">
                <th className="px-6 py-4 border-b border-green-100">#</th>
                <th className="px-6 py-4 border-b border-green-100">School ID</th>
                <th className="px-6 py-4 border-b border-green-100">Name</th>
                <th className="px-6 py-4 border-b border-green-100">Class</th>
                <th className="px-6 py-4 border-b border-green-100 text-right">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-green-100">
              {filtered.map((student, idx) => (
                <tr key={student.id} className="hover:bg-green-50/50 transition-colors group">
                  <td className="px-6 py-4 text-sm text-green-700">{idx + 1}</td>
                  <td className="px-6 py-4 text-sm font-mono text-green-700">{student.school_id}</td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center overflow-hidden border border-green-200">
                        {student.avatar ? <img src={student.avatar} className="w-full h-full object-cover" /> : <span className="text-xs font-bold text-green-400">{student.firstname[0]}</span>}
                      </div>
                      <span className="text-sm font-medium text-green-950">{student.firstname} {student.lastname}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-sm text-green-700">{getClassName(student.class_id)}</td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex items-center justify-end gap-2">
                      <button className="p-2 hover:bg-green-100 rounded-lg text-green-400 hover:text-green-700 transition-all"><Eye className="w-4 h-4" /></button>
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
    </div>
  );
}
