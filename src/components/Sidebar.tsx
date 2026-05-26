import React from 'react';
import { useAuthStore } from '../store';
import { 
  LayoutDashboard, 
  Users, 
  GraduationCap, 
  BookOpen, 
  Settings, 
  LogOut, 
  ClipboardCheck,
  Layers,
  FileText,
  Calendar
} from 'lucide-react';
import { Link, useLocation } from 'react-router-dom';

export default function Sidebar() {
  const { type, logout, sidebarOpen, setSidebar } = useAuthStore();
  const location = useLocation();

  const menuItems = {
    superadmin: [
      { name: 'Dashboard', icon: LayoutDashboard, path: '/index.php' },
      { name: 'Faculties', icon: GraduationCap, path: '/faculties.php' },
      { name: 'Students', icon: Users, path: '/students.php' },
      { name: 'Admin Users', icon: Settings, path: '/admins.php' },
      { name: 'Super Admins', icon: Users, path: '/superadmins.php' },
      { name: 'Backup/CSV', icon: FileText, path: '/backup.php' },
    ],
    admin: [
      { name: 'Dashboard', icon: LayoutDashboard, path: '/index.php' },
      { name: 'Academic Year', icon: Calendar, path: '/academic.php' },
      { name: 'Classes', icon: Layers, path: '/classes.php' },
      { name: 'Subjects', icon: BookOpen, path: '/subjects.php' },
      { name: 'Criteria', icon: ClipboardCheck, path: '/criteria.php' },
      { name: 'Questionnaire', icon: FileText, path: '/questionnaire.php' },
      { name: 'Evaluation Report', icon: FileText, path: '/report.php' },
    ],
    faculty: [
      { name: 'Dashboard', icon: LayoutDashboard, path: '/index.php' },
      { name: 'Evaluation Result', icon: ClipboardCheck, path: '/result.php' },
    ],
    student: [
      { name: 'Dashboard', icon: LayoutDashboard, path: '/index.php' },
      { name: 'Evaluate', icon: ClipboardCheck, path: '/evaluate.php' },
    ]
  };

  const currentMenu = menuItems[type as keyof typeof menuItems] || [];

  return (
    <>
      {/* Overlay */}
      {sidebarOpen && (
        <div 
          className="fixed inset-0 bg-black/50 z-40 lg:hidden animate-in fade-in duration-300"
          onClick={() => setSidebar(false)}
        />
      )}

      <div className={`
        fixed inset-y-0 left-0 z-50 w-64 bg-green-950 text-green-300 flex flex-col border-r border-white/5 transition-transform duration-300 transform
        ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
        lg:translate-x-0 lg:static min-h-screen
      `}>
        <div className="p-6 border-b border-white/5 flex items-center justify-between lg:justify-center">
          <div className="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center shadow-lg shadow-green-600/20">
            <ClipboardCheck className="text-white w-7 h-7" />
          </div>
          <button 
            onClick={() => setSidebar(false)}
            className="lg:hidden text-green-300 hover:text-white transition-all"
          >
            <LogOut className="w-6 h-6 rotate-180" />
          </button>
        </div>
      <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
        {currentMenu.map((item) => (
          <Link
            key={item.path}
            to={item.path}
            className={`flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${
              location.pathname === item.path 
                ? 'bg-green-600/10 text-green-400' 
                : 'hover:bg-white/5 hover:text-white'
            }`}
          >
            <item.icon className="w-5 h-5" />
            <span className="font-medium">{item.name}</span>
          </Link>
        ))}
      </nav>

      <div className="p-4 border-t border-white/5">
        <button
          onClick={logout}
          className="flex items-center gap-3 px-4 py-3 w-full rounded-xl hover:bg-red-500/10 hover:text-red-400 transition-all text-green-400"
        >
          <LogOut className="w-5 h-5" />
          <span className="font-medium">Logout</span>
        </button>
      </div>
    </div>
  </>
);
}
