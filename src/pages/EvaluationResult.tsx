import React, { useEffect, useState } from 'react';
import { useAuthStore } from '../store';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { MessageSquare, Star, Printer } from 'lucide-react';
import { API_BASE } from '../constants';

export default function EvaluationResult() {
  const { user, academic } = useAuthStore();
  const [results, setResults] = useState<any[]>([]);
  const [comments, setComments] = useState<any[]>([]);
  const [criteria, setCriteria] = useState<any[]>([]);

  useEffect(() => {
    // In a real app, this would fetch aggregated data for the specific faculty
    // For now, we'll mock some data based on criteria
    fetch(`${API_BASE}/criteria_list.php`).then(res => res.json()).then(data => {
      setCriteria(data);
      const mockResults = data.map((c: any) => ({
        name: c.criteria,
        rating: (Math.random() * 2 + 3).toFixed(2), // Random rating between 3 and 5
      }));
      setResults(mockResults);
    });

    // Mock comments
    setComments([
      { id: 1, comment: "Very clear explanations and very approachable.", student: "Anonymous" },
      { id: 2, comment: "Sometimes the pace is a bit fast, but overall great.", student: "Anonymous" },
      { id: 3, comment: "Excellent teaching style!", student: "Anonymous" },
    ]);
  }, []);

  const overallRating = (results.reduce((acc, curr) => acc + parseFloat(curr.rating), 0) / results.length).toFixed(2);

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-green-950">Evaluation Results</h1>
          <p className="text-green-700">Academic Year: {academic?.year} | {academic?.semester === 1 ? '1st' : '2nd'} Sem</p>
        </div>
        <button className="bg-green-950 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-green-900 transition-all shadow-lg">
          <Printer className="w-5 h-5" />
          Print Report
        </button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-1 space-y-8">
          <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-sm text-center">
            <p className="text-green-700 font-medium mb-2 uppercase tracking-widest text-xs">Overall Rating</p>
            <div className="text-6xl font-black text-green-950 mb-4">{overallRating}</div>
            <div className="flex justify-center gap-1 mb-4">
              {[1, 2, 3, 4, 5].map(star => (
                <Star key={star} className={`w-6 h-6 ${star <= Math.round(parseFloat(overallRating)) ? 'text-yellow-400 fill-yellow-400' : 'text-green-200'}`} />
              ))}
            </div>
            <p className="text-sm text-green-700 italic">"Excellent Performance"</p>
          </div>

          <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-sm">
            <h3 className="font-bold text-green-900 mb-6 flex items-center gap-2">
              <MessageSquare className="w-5 h-5 text-green-600" />
              Student Comments
            </h3>
            <div className="space-y-4">
              {comments.map(c => (
                <div key={c.id} className="p-4 rounded-2xl bg-green-50 border border-green-100">
                  <p className="text-green-800 text-sm italic mb-2">"{c.comment}"</p>
                  <p className="text-[10px] font-bold text-green-400 uppercase tracking-wider">— {c.student}</p>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="lg:col-span-2">
          <div className="bg-white p-8 rounded-3xl border border-green-200 shadow-sm h-full">
            <h3 className="font-bold text-green-900 mb-8">Rating Breakdown by Criteria</h3>
            <div className="h-[400px]">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={results} layout="vertical" margin={{ left: 40, right: 40 }}>
                  <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="#f0fdf4" />
                  <XAxis type="number" domain={[0, 5]} hide />
                  <YAxis 
                    dataKey="name" 
                    type="category" 
                    width={150} 
                    axisLine={false} 
                    tickLine={false}
                    tick={{ fill: '#15803d', fontSize: 12, fontWeight: 500 }}
                  />
                  <Tooltip 
                    cursor={{ fill: '#f0fdf4' }}
                    contentStyle={{ borderRadius: '12px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)' }}
                  />
                  <Bar dataKey="rating" radius={[0, 8, 8, 0]} barSize={32}>
                    {results.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={parseFloat(entry.rating) > 4 ? '#16a34a' : '#f59e0b'} />
                    ))}
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            </div>
            <div className="mt-8 grid grid-cols-2 gap-4">
              {results.map(r => (
                <div key={r.name} className="flex items-center justify-between p-3 rounded-xl bg-green-50 border border-green-100">
                  <span className="text-xs font-medium text-green-700 truncate mr-2">{r.name}</span>
                  <span className="text-sm font-bold text-green-950">{r.rating}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
