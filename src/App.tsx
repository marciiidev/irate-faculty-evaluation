/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useAuthStore } from './store';
import Login from './Login';
import Layout from './Layout';
import Dashboard from './pages/Dashboard';
import FacultyList from './pages/FacultyList';
import StudentList from './pages/StudentList';
import AcademicYear from './pages/AcademicYear';
import ClassList from './pages/ClassList';
import SubjectList from './pages/SubjectList';
import CriteriaList from './pages/CriteriaList';
import Questionnaire from './pages/Questionnaire';
import Evaluate from './pages/Evaluate';
import EvaluationResult from './pages/EvaluationResult';
import ManageAccount from './pages/ManageAccount';
import UserList from './pages/UserList';
import EvaluationReport from './pages/EvaluationReport';
import Backup from './pages/Backup';

export default function App() {
  const { user } = useAuthStore();

  return (
    <BrowserRouter basename="/faculty-evaluation-system">
      {!user ? (
        <Login />
      ) : (
        <Routes>
          <Route element={<Layout />}>
            <Route path="/" element={<Navigate to="/index.php" replace />} />
            <Route path="/index.php" element={<Dashboard />} />
            <Route path="/faculties.php" element={<FacultyList />} />
            <Route path="/students.php" element={<StudentList />} />
            <Route path="/academic.php" element={<AcademicYear />} />
            <Route path="/classes.php" element={<ClassList />} />
            <Route path="/subjects.php" element={<SubjectList />} />
            <Route path="/criteria.php" element={<CriteriaList />} />
            <Route path="/questionnaire.php" element={<Questionnaire />} />
            <Route path="/evaluate.php" element={<Evaluate />} />
            <Route path="/result.php" element={<EvaluationResult />} />
            <Route path="/report.php" element={<EvaluationReport />} />
            <Route path="/admins.php" element={<UserList role="admin" />} />
            <Route path="/superadmins.php" element={<UserList role="superadmin" />} />
            <Route path="/backup.php" element={<Backup />} />
            <Route path="/settings.php" element={<ManageAccount />} />
            <Route path="*" element={<Navigate to="/index.php" replace />} />
          </Route>
        </Routes>
      )}
    </BrowserRouter>
  );
}
