import { create } from 'zustand';

interface AuthState {
  user: any | null;
  type: 'superadmin' | 'admin' | 'faculty' | 'student' | null;
  academic: any | null;
  sidebarOpen: boolean;
  login: (user: any, type: any, academic: any) => void;
  logout: () => void;
  toggleSidebar: () => void;
  setSidebar: (open: boolean) => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: JSON.parse(localStorage.getItem('user') || 'null'),
  type: localStorage.getItem('type') as any || null,
  academic: JSON.parse(localStorage.getItem('academic') || 'null'),
  sidebarOpen: false,
  login: (user, type, academic) => {
    localStorage.setItem('user', JSON.stringify(user));
    localStorage.setItem('type', type);
    localStorage.setItem('academic', JSON.stringify(academic));
    set({ user, type, academic });
  },
  logout: () => {
    localStorage.clear();
    set({ user: null, type: null, academic: null, sidebarOpen: false });
  },
  toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
  setSidebar: (open) => set({ sidebarOpen: open }),
}));
