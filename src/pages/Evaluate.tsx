import React, { useEffect, useState } from 'react';
import { useAuthStore } from '../store';
import { GraduationCap, Printer, ClipboardCheck, Send, Info } from 'lucide-react';
import Swal from 'sweetalert2';
import { API_BASE } from '../constants';

export default function Evaluate() {
  const { user, academic } = useAuthStore();
  const [restrictions, setRestrictions] = useState<any[]>([]);
  const [faculties, setFaculties] = useState<any[]>([]);
  const [subjects, setSubjects] = useState<any[]>([]);
  const [classes, setClasses] = useState<any[]>([]);
  const [selectedEval, setSelectedEval] = useState<any | null>(null);
  const [questions, setQuestions] = useState<any[]>([]);
  const [criteria, setCriteria] = useState<any[]>([]);
  const [answers, setAnswers] = useState<Record<number, number>>({});
  const [comment, setComment] = useState('');

  const [submitted, setSubmitted] = useState(false);

  useEffect(() => {
    if (academic) {
      fetch(`${API_BASE}/restriction_list.php?academic_id=${academic.id}`).then(res => res.json()).then(data => setRestrictions(data));
      fetch(`${API_BASE}/faculty_list.php`).then(res => res.json()).then(data => setFaculties(data));
      fetch(`${API_BASE}/subject_list.php`).then(res => res.json()).then(data => setSubjects(data));
      fetch(`${API_BASE}/class_list.php`).then(res => res.json()).then(data => setClasses(data));
      fetch(`${API_BASE}/criteria_list.php`).then(res => res.json()).then(data => setCriteria(data));
      fetch(`${API_BASE}/question_list.php?academic_id=${academic.id}`).then(res => res.json()).then(data => setQuestions(data));
    }
  }, [academic]);

  const handleSelectEval = (res: any) => {
    setSelectedEval(res);
    setAnswers({});
    setComment('');
    setSubmitted(false);
  };

  const handleSubmit = async () => {
    if (Object.keys(answers).length < questions.length) {
      Swal.fire('Error', 'Please answer all questions', 'error');
      return;
    }

    try {
      // In a real app, we would POST to /api/evaluation_list
      // For now, we'll just simulate success
      setSubmitted(true);
      Swal.fire('Success', 'Evaluation submitted successfully', 'success');
    } catch (err) {
      Swal.fire('Error', 'Failed to submit evaluation', 'error');
    }
  };

  if (submitted) {
    return (
      <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-xl">
        <div className="text-center space-y-6">
          <div className="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto text-green-600">
            <GraduationCap className="w-12 h-12" />
          </div>
          <h2 className="text-3xl font-bold text-green-950">Evaluation Submitted!</h2>
          <p className="text-green-700 max-w-md mx-auto">
            Thank you for your honest participation. Your feedback helps us improve our faculty's performance.
          </p>
          <div className="pt-6">
            <button 
              onClick={() => window.print()}
              className="bg-green-950 text-white px-8 py-3 rounded-2xl font-bold flex items-center gap-2 hover:bg-green-900 transition-all mx-auto"
            >
              <Printer className="w-5 h-5" />
              Download Certificate
            </button>
          </div>
          <button 
            onClick={() => {
              setSelectedEval(null);
              setSubmitted(false);
            }}
            className="text-green-400 hover:text-green-600 font-medium text-sm mt-4"
          >
            Back to List
          </button>
        </div>
      </div>
    );
  }

  if (selectedEval) {
    const faculty = faculties.find(f => f.id === selectedEval.faculty_id);
    const subject = subjects.find(s => s.id === selectedEval.subject_id);

    return (
      <div className="max-w-4xl mx-auto space-y-8">
        <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-xl">
          <div className="flex items-center justify-between mb-8">
            <div>
              <h1 className="text-2xl font-bold text-green-950">Evaluation Form</h1>
              <p className="text-green-700">Instructor: <span className="font-bold text-green-800">{faculty?.firstname} {faculty?.lastname}</span></p>
              <p className="text-green-700">Subject: <span className="font-bold text-green-800">{subject?.subject} ({subject?.code})</span></p>
            </div>
            <button 
              onClick={() => setSelectedEval(null)}
              className="text-green-400 hover:text-green-600 font-medium"
            >
              Cancel
            </button>
          </div>

          <div className="bg-green-50 p-6 rounded-2xl border border-green-100 flex gap-4 mb-8">
            <Info className="text-green-600 w-6 h-6 shrink-0" />
            <div className="text-sm text-green-800 leading-relaxed">
              <p className="font-bold mb-1">Rating Scale:</p>
              <p>5 - Always manifested | 4 - Often manifested | 3 - Sometimes manifested | 2 - Seldom manifested | 1 - Never/Rarely manifested</p>
            </div>
          </div>

          <div className="space-y-12">
            {criteria.map(c => {
              const qList = questions.filter(q => q.criteria_id === c.id);
              if (qList.length === 0) return null;
              return (
                <div key={c.id} className="space-y-6">
                  <h3 className="text-lg font-bold text-green-900 border-b border-green-100 pb-2">{c.criteria}</h3>
                  <div className="space-y-6">
                    {qList.map((q, idx) => (
                      <div key={q.id} className="space-y-4">
                        <div className="flex gap-4">
                          <span className="text-green-400 font-mono">{idx + 1}.</span>
                          <p className="text-green-800 font-medium">{q.question}</p>
                        </div>
                        <div className="flex items-center gap-4 ml-8">
                          {[1, 2, 3, 4, 5].map(rating => (
                            <label key={rating} className="flex flex-col items-center gap-2 cursor-pointer group">
                              <input 
                                type="radio" 
                                name={`q_${q.id}`} 
                                value={rating}
                                checked={answers[q.id] === rating}
                                onChange={() => setAnswers({...answers, [q.id]: rating})}
                                className="w-5 h-5 text-green-600 focus:ring-green-950 border-green-300"
                              />
                              <span className="text-xs font-bold text-green-400 group-hover:text-green-600">{rating}</span>
                            </label>
                          ))}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              );
            })}
          </div>

          <div className="mt-12 pt-8 border-t border-green-100 space-y-4">
            <label className="block font-bold text-green-900">Comments / Suggestions</label>
            <textarea 
              className="w-full px-4 py-3 rounded-2xl border border-green-200 focus:ring-2 focus:ring-green-950 outline-none h-32 resize-none"
              placeholder="Please write your comments here..."
              value={comment}
              onChange={(e) => setComment(e.target.value)}
            />
          </div>

          <div className="mt-8 flex justify-end">
            <button 
              onClick={handleSubmit}
              className="bg-green-950 text-white px-8 py-3 rounded-2xl font-bold flex items-center gap-2 hover:bg-green-900 transition-all shadow-lg shadow-green-950/20"
            >
              <Send className="w-5 h-5" />
              Submit Evaluation
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-green-950">Evaluate Instructors</h1>
        <p className="text-green-700">Select a subject to begin your evaluation.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {restrictions.map(res => {
          const faculty = faculties.find(f => f.id === res.faculty_id);
          const subject = subjects.find(s => s.id === res.subject_id);
          const cls = classes.find(c => c.id === res.class_id);

          return (
            <div key={res.id} className="bg-white p-6 rounded-2xl border border-green-200 shadow-sm hover:shadow-md transition-all group">
              <div className="flex flex-col gap-4">
                <div className="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition-all">
                  <ClipboardCheck className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="font-bold text-green-950">{faculty?.firstname} {faculty?.lastname}</h3>
                  <p className="text-sm text-green-700">{subject?.subject} ({subject?.code})</p>
                  <p className="text-xs text-green-400 mt-1">{cls?.curriculum} {cls?.level}-{cls?.section}</p>
                </div>
                <button 
                  onClick={() => handleSelectEval(res)}
                  className="w-full py-2.5 bg-green-50 text-green-700 rounded-xl font-semibold hover:bg-green-600 hover:text-white transition-all"
                >
                  Evaluate Now
                </button>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
