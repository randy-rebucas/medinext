import React from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Calendar,
  Users,
  FileText,
  Pill,
  Settings,
  ArrowRight
} from 'lucide-react';

interface AdminNavigationProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      role: string;
    };
  };
}

export default function AdminNavigation({ auth }: AdminNavigationProps) {
  const adminFeatures = [
    {
      title: 'My Clinical Schedule',
      description: 'View your patient appointments and clinical availability',
      icon: Calendar,
      href: '/doctor/dashboard?tab=schedule',
      color: 'bg-blue-500',
    },
    {
      title: 'Patient Medical Records',
      description: 'Access and manage patient electronic medical records',
      icon: Users,
      href: '/doctor/dashboard?tab=emr',
      color: 'bg-green-500',
    },
    {
      title: 'Prescription Management',
      description: 'Create and manage patient prescriptions and medication orders',
      icon: FileText,
      href: '/doctor/dashboard?tab=prescriptions',
      color: 'bg-purple-500',
    },
    {
      title: 'Medication Samples',
      description: 'Review samples from pharmaceutical representatives',
      icon: Pill,
      href: '/doctor/dashboard?tab=meds-samples',
      color: 'bg-orange-500',
    },
  ];

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Doctor Dashboard
          </h1>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Access your clinical practice management tools. View your patient schedule, manage medical records,
            issue prescriptions, and review medication samples from pharmaceutical representatives.
          </p>
        </div>

        {/* Quick Access Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
          {adminFeatures.map((feature, index) => (
            <Card key={index} className="hover:shadow-lg transition-shadow cursor-pointer group">
              <Link href={feature.href}>
                <CardHeader className="pb-3">
                  <div className="flex items-center space-x-3">
                    <div className={`p-3 rounded-lg ${feature.color} group-hover:scale-110 transition-transform`}>
                      <feature.icon className="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <CardTitle className="text-lg">{feature.title}</CardTitle>
                      <CardDescription className="text-sm">
                        {feature.description}
                      </CardDescription>
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="pt-0">
                  <div className="flex items-center text-blue-600 group-hover:text-blue-800">
                    <span className="text-sm font-medium">Open</span>
                    <ArrowRight className="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform" />
                  </div>
                </CardContent>
              </Link>
            </Card>
          ))}
        </div>

        {/* Full Dashboard Access */}
        <Card className="text-center">
          <CardHeader>
            <CardTitle className="text-2xl">Complete Doctor Dashboard</CardTitle>
            <CardDescription>
              Access all clinical features in one comprehensive dashboard
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Link href="/doctor/dashboard">
              <Button size="lg" className="px-8">
                <Settings className="h-5 w-5 mr-2" />
                Open Doctor Dashboard
              </Button>
            </Link>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
