import React from 'react';
import { Head } from '@inertiajs/react';
import AdminNavigation from '@/components/admin/AdminNavigation';

interface AdminIndexProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
}

export default function AdminIndex({ auth }: AdminIndexProps) {
  return (
    <>
      <Head title="Doctor Dashboard - MediNext" />
      <AdminNavigation auth={auth} />
    </>
  );
}
