import React, { useState, useEffect } from 'react';
import { useAuthStore } from '../store';
import { 
  Users, 
  GraduationCap, 
  Layers, 
  Calendar,
  CheckCircle2,
  Clock,
  AlertCircle
} from 'lucide-react';

export default function Dashboard() {
  const { user, type, academic } = useAuthStore();
  const [currentTime, setCurrentTime] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentTime(new Date());
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const stats = [
    { name: 'Total Faculties', value: '45', icon: GraduationCap, color: 'bg-green-600' },
    { name: 'Total Students', value: '1,240', icon: Users, color: 'bg-green-700' },
    { name: 'Total Classes', value: '24', icon: Layers, color: 'bg-green-800' },
  ];

  return (
    <div className="space-y-8">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex flex-col gap-1">
          <h1 className="text-3xl font-bold text-green-950">Welcome, {user?.firstname}!</h1>
          <p className="text-green-700">Here's what's happening in the system today.</p>
        </div>
        <div className="bg-white px-6 py-4 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
          <div className="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
            <Clock className="text-green-700 w-5 h-5" />
          </div>
          <div className="text-right">
            <p className="text-sm font-bold text-green-950">
              {currentTime.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
            </p>
            <p className="text-2xl font-black text-green-600 tabular-nums">
              {currentTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
            </p>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
          <div className="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
            <Calendar className="text-green-700 w-6 h-6" />
          </div>
          <div>
            <p className="text-sm text-green-700 font-medium">Academic Year</p>
            <p className="text-lg font-bold text-green-950">{academic?.year || 'Not Set'}</p>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
          <div className="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
            <Clock className="text-green-700 w-6 h-6" />
          </div>
          <div>
            <p className="text-sm text-green-700 font-medium">Semester</p>
            <p className="text-lg font-bold text-green-950">
              {academic?.semester === 1 ? '1st' : academic?.semester === 2 ? '2nd' : 'N/A'} Semester
            </p>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-green-200 shadow-sm flex items-center gap-4">
          <div className="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
            <CheckCircle2 className="text-green-700 w-6 h-6" />
          </div>
          <div>
            <p className="text-sm text-green-700 font-medium">Evaluation Status</p>
            <span className={`px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${
              academic?.status === 1 ? 'bg-green-100 text-green-800' : 
              academic?.status === 2 ? 'bg-red-100 text-red-700' : 'bg-green-50 text-green-700'
            }`}>
              {academic?.status === 1 ? 'Ongoing' : academic?.status === 2 ? 'Closed' : 'Pending'}
            </span>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {stats.map((stat) => (
          <div key={stat.name} className="bg-white p-8 rounded-2xl border border-green-200 shadow-sm relative overflow-hidden group">
            <div className={`absolute top-0 right-0 w-32 h-32 ${stat.color} opacity-5 -mr-8 -mt-8 rounded-full transition-transform group-hover:scale-110`} />
            <div className="flex flex-col gap-4">
              <div className={`w-12 h-12 ${stat.color} rounded-xl flex items-center justify-center text-white shadow-lg shadow-current/20`}>
                <stat.icon className="w-6 h-6" />
              </div>
              <div>
                <p className="text-4xl font-bold text-green-950">{stat.value}</p>
                <p className="text-green-700 font-medium">{stat.name}</p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
