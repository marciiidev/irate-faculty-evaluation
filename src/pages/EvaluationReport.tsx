import React, { useEffect, useState } from 'react';
import { Search, Printer, FileText, ChevronRight } from 'lucide-react';
import { API_BASE } from '../constants';

export default function EvaluationReport() {
  const [faculties, setFaculties] = useState<any[]>([]);
  const [selectedFaculty, setSelectedFaculty] = useState('');
  const [reportData, setReportData] = useState<any | null>(null);

  useEffect(() => {
    fetch(`${API_BASE}/faculty_list.php`).then(res => res.json()).then(data => setFaculties(data));
  }, []);

  const handleGenerate = () => {
    if (!selectedFaculty) return;
    // Mock report generation
    setReportData({
      faculty: faculties.find(f => f.id.toString() === selectedFaculty),
      overall: "4.52",
      breakdown: [
        { criteria: "Instructional Skills", rating: "4.60" },
        { criteria: "Classroom Management", rating: "4.45" },
        { criteria: "Professionalism", rating: "4.55" },
      ]
    });
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-green-950">Evaluation Reports</h1>
        <p className="text-green-700">Generate and print evaluation summaries for faculty members.</p>
      </div>

      <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-sm">
        <div className="flex flex-col md:flex-row items-end gap-6">
          <div className="flex-1 space-y-2">
            <label className="text-sm font-bold text-green-800">Select Faculty Member</label>
            <select 
              className="w-full px-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none"
              value={selectedFaculty}
              onChange={(e) => setSelectedFaculty(e.target.value)}
            >
              <option value="">-- Choose Faculty --</option>
              {faculties.map(f => <option key={f.id} value={f.id}>{f.firstname} {f.lastname} ({f.school_id})</option>)}
            </select>
          </div>
          <button 
            onClick={handleGenerate}
            className="bg-green-950 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-green-900 transition-all shadow-lg shadow-green-950/20"
          >
            Generate Report
          </button>
        </div>
      </div>

      {reportData && (
        <div className="bg-white rounded-3xl border border-green-200 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div className="p-8 border-b border-green-100 flex items-center justify-between bg-green-50/50">
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center text-green-600">
                <FileText className="w-8 h-8" />
              </div>
              <div>
                <h2 className="text-xl font-bold text-green-950">{reportData.faculty.firstname} {reportData.faculty.lastname}</h2>
                <p className="text-green-700 text-sm">Faculty ID: {reportData.faculty.school_id}</p>
              </div>
            </div>
            <button className="bg-green-950 text-white px-6 py-2.5 rounded-xl font-bold flex items-center gap-2 hover:bg-green-900 transition-all">
              <Printer className="w-5 h-5" />
              Print Report
            </button>
          </div>
          <div className="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="md:col-span-1 bg-green-950 text-white p-8 rounded-3xl flex flex-col items-center justify-center text-center">
              <p className="text-green-400 text-xs uppercase tracking-widest font-bold mb-2">Overall Rating</p>
              <div className="text-6xl font-black mb-2">{reportData.overall}</div>
              <p className="text-green-400 font-bold">Outstanding</p>
            </div>
            <div className="md:col-span-2 space-y-4">
              <h3 className="font-bold text-green-900 mb-4">Criteria Breakdown</h3>
              {reportData.breakdown.map((item: any) => (
                <div key={item.criteria} className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="font-medium text-green-700">{item.criteria}</span>
                    <span className="font-bold text-green-950">{item.rating} / 5.00</span>
                  </div>
                  <div className="w-full h-2 bg-green-100 rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-green-600 rounded-full" 
                      style={{ width: `${(parseFloat(item.rating) / 5) * 100}%` }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
