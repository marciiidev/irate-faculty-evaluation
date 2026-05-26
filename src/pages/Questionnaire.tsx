import React, { useEffect, useState } from 'react';
import { Plus, Search, Edit, Trash2, FileText } from 'lucide-react';
import Swal from 'sweetalert2';
import { API_BASE } from '../constants';

export default function Questionnaire() {
  const [academics, setAcademics] = useState<any[]>([]);
  const [selectedAcademic, setSelectedAcademic] = useState<string>('');
  const [questions, setQuestions] = useState<any[]>([]);
  const [criteria, setCriteria] = useState<any[]>([]);

  useEffect(() => {
    fetch(`${API_BASE}/academic_list.php`).then(res => res.json()).then(data => {
      setAcademics(data);
      const def = data.find((a: any) => a.is_default === 1);
      if (def) setSelectedAcademic(def.id.toString());
    });
    fetch(`${API_BASE}/criteria_list.php`).then(res => res.json()).then(data => setCriteria(data));
  }, []);

  useEffect(() => {
    if (selectedAcademic) {
      fetch(`${API_BASE}/question_list.php?academic_id=${selectedAcademic}`)
        .then(res => res.json())
        .then(data => setQuestions(data));
    }
  }, [selectedAcademic]);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-green-950">Questionnaire Management</h1>
          <p className="text-green-700">Manage evaluation questions for each academic year.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-1 space-y-6">
          <div className="bg-white p-6 rounded-2xl border border-green-200 shadow-sm">
            <label className="block text-sm font-semibold text-green-800 mb-2">Select Academic Year</label>
            <select 
              className="w-full px-4 py-2.5 rounded-xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none"
              value={selectedAcademic}
              onChange={(e) => setSelectedAcademic(e.target.value)}
            >
              {academics.map(a => (
                <option key={a.id} value={a.id}>{a.year} - {a.semester === 1 ? '1st' : '2nd'} Sem</option>
              ))}
            </select>
          </div>

          <div className="bg-white p-6 rounded-2xl border border-green-200 shadow-sm">
            <h3 className="font-bold text-green-900 mb-4 flex items-center gap-2">
              <Plus className="w-5 h-5 text-green-600" />
              Add New Question
            </h3>
            <form className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-green-700 uppercase mb-1">Criteria</label>
                <select className="w-full px-3 py-2 rounded-lg border border-green-200 text-sm outline-none">
                  <option value="">Select Criteria</option>
                  {criteria.map(c => <option key={c.id} value={c.id}>{c.criteria}</option>)}
                </select>
              </div>
              <div>
                <label className="block text-xs font-bold text-green-700 uppercase mb-1">Question</label>
                <textarea 
                  className="w-full px-3 py-2 rounded-lg border border-green-200 text-sm outline-none h-24 resize-none"
                  placeholder="Enter evaluation question..."
                />
              </div>
              <button className="w-full bg-green-950 text-white py-2 rounded-lg font-semibold hover:bg-green-900 transition-all">
                Save Question
              </button>
            </form>
          </div>
        </div>

        <div className="lg:col-span-2">
          <div className="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
            <div className="p-4 border-b border-green-100 bg-green-50/50">
              <h3 className="font-bold text-green-900">Evaluation Questionnaire</h3>
            </div>
            <div className="p-6 space-y-8">
              {criteria.map(c => {
                const qList = questions.filter(q => q.criteria_id === c.id);
                if (qList.length === 0) return null;
                return (
                  <div key={c.id} className="space-y-4">
                    <h4 className="text-sm font-bold text-green-700 uppercase tracking-wider bg-green-100 px-3 py-1 rounded-lg inline-block">
                      {c.criteria}
                    </h4>
                    <div className="space-y-2">
                      {qList.map((q, idx) => (
                        <div key={q.id} className="flex items-start justify-between p-4 rounded-xl border border-green-100 hover:border-green-200 hover:bg-green-50/50 transition-all group">
                          <div className="flex gap-4">
                            <span className="text-green-400 font-mono text-sm">{idx + 1}.</span>
                            <p className="text-green-800 text-sm leading-relaxed">{q.question}</p>
                          </div>
                          <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button className="p-2 text-green-400 hover:text-green-600"><Edit className="w-4 h-4" /></button>
                            <button className="p-2 text-green-400 hover:text-red-600"><Trash2 className="w-4 h-4" /></button>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                );
              })}
              {questions.length === 0 && (
                <div className="text-center py-12">
                  <FileText className="w-12 h-12 text-green-200 mx-auto mb-4" />
                  <p className="text-green-700 italic">No questions added for this academic year yet.</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
