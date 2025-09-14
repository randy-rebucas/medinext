import { dashboard, login, register } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { useState, useEffect } from 'react';

interface PageProps extends InertiaPageProps {
    auth: {
        user?: {
            id: number;
            name: string;
            email: string;
            role: string;
        };
    };
}

export default function Welcome() {
    const { auth } = usePage<PageProps>().props;
    const [currentTestimonial, setCurrentTestimonial] = useState(0);
    const [isVisible, setIsVisible] = useState(false);

    const testimonials = [
        {
            name: "Dr. Sarah Johnson",
            role: "Chief Medical Officer",
            organization: "Metro Health Center",
            content: "Medinext has revolutionized our patient care workflow. The intuitive interface and comprehensive features have improved our efficiency by 40%.",
            rating: 5
        },
        {
            name: "Dr. Michael Chen",
            role: "Family Physician",
            organization: "Community Health Clinic",
            content: "The prescription management system is outstanding. Drug interaction alerts have prevented several potential issues in our practice.",
            rating: 5
        },
        {
            name: "Dr. Emily Rodriguez",
            role: "Pediatrician",
            organization: "Children's Medical Group",
            content: "Patient scheduling has never been easier. The automated reminders have significantly reduced no-shows.",
            rating: 5
        }
    ];

    useEffect(() => {
        setIsVisible(true);
        const interval = setInterval(() => {
            setCurrentTestimonial((prev) => (prev + 1) % testimonials.length);
        }, 5000);
        return () => clearInterval(interval);
    }, [testimonials.length]);

    return (
        <>
            <Head title="Welcome to Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>

            {/* Background with gradient */}
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                {/* Navigation */}
                <nav className="relative z-10 px-4 py-6 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        <div className="flex items-center justify-between">
                            {/* Logo */}
                            <div className="flex items-center space-x-2">
                                <div className="h-8 w-8 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                    <span className="text-white font-bold text-sm">M</span>
                                </div>
                                <span className="text-xl font-bold text-slate-900 dark:text-white">Medinext</span>
                            </div>

                            {/* Navigation Links */}
                            <div className="flex items-center space-x-4">
                                {auth.user ? (
                                    <Link
                                        href={dashboard()}
                                        className="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                                    >
                                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
                                        </svg>
                                        Dashboard
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={login()}
                                            className="text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white px-4 py-2 text-sm font-medium transition-colors duration-200"
                                        >
                                            Sign In
                                        </Link>
                                        <Link
                                            href={register()}
                                            className="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                                        >
                                            Get Started
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </nav>

                {/* Hero Section */}
                <main className="relative">
                    {/* Animated Background Elements */}
                    <div className="absolute inset-0 overflow-hidden">
                        <div className="absolute -top-40 -right-32 w-80 h-80 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"></div>
                        <div className="absolute -bottom-40 -left-32 w-80 h-80 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse" style={{animationDelay: '2s'}}></div>
                    </div>

                    <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20 relative z-10">
                        <div className="text-center">
                            {/* Trust Badge */}
                            <div className={`inline-flex items-center px-4 py-2 rounded-full bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border border-slate-200 dark:border-slate-700 mb-8 transition-all duration-1000 ${isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                                <svg className="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                </svg>
                                <span className="text-sm font-medium text-slate-700 dark:text-slate-300">HIPAA Compliant • SOC 2 Certified</span>
                            </div>

                            {/* Main Heading */}
                            <h1 className={`text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl transition-all duration-1000 delay-200 ${isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                                Welcome to{' '}
                                <span className="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent animate-pulse">
                                    Medinext
                                </span>
                            </h1>

                            {/* Subtitle */}
                            <p className={`mx-auto mt-6 max-w-3xl text-lg leading-8 text-slate-600 dark:text-slate-300 transition-all duration-1000 delay-400 ${isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                                The most comprehensive Electronic Medical Records (EMR) solution for modern healthcare.
                                Streamline patient care, manage appointments, and enhance healthcare delivery with cutting-edge technology.
                            </p>

                            {/* Enhanced Statistics */}
                            <div className={`mt-12 grid grid-cols-1 gap-8 sm:grid-cols-3 transition-all duration-1000 delay-600 ${isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'}`}>
                                <div className="text-center group">
                                    <div className="text-4xl font-bold text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform duration-300">10,000+</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-300 mt-1">Healthcare Professionals</div>
                                    <div className="w-12 h-1 bg-blue-600 mx-auto mt-2 rounded-full"></div>
                                </div>
                                <div className="text-center group">
                                    <div className="text-4xl font-bold text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform duration-300">500,000+</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-300 mt-1">Patient Records</div>
                                    <div className="w-12 h-1 bg-purple-600 mx-auto mt-2 rounded-full"></div>
                                </div>
                                <div className="text-center group">
                                    <div className="text-4xl font-bold text-green-600 dark:text-green-400 group-hover:scale-110 transition-transform duration-300">99.9%</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-300 mt-1">Uptime Guarantee</div>
                                    <div className="w-12 h-1 bg-green-600 mx-auto mt-2 rounded-full"></div>
                                </div>
                            </div>

                            {/* CTA Buttons */}
                            <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                {!auth.user && (
                                    <>
                                        <Link
                                            href={register()}
                                            className="inline-flex items-center px-8 py-3 text-base font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                                        >
                                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Get Started Free
                                        </Link>
                                        <Link
                                            href={login()}
                                            className="inline-flex items-center px-8 py-3 text-base font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 shadow-sm hover:shadow-md"
                                        >
                                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            Sign In
                                        </Link>
                                    </>
                                )}
                                {auth.user && (
                                    <Link
                                        href={dashboard()}
                                        className="inline-flex items-center px-8 py-3 text-base font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                                    >
                                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
                                        </svg>
                                        Go to Dashboard
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Features Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                Everything you need for modern healthcare
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                Comprehensive EMR features designed for healthcare professionals
                            </p>
                        </div>

                        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            {/* Feature 1 */}
                            <div className="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div className="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg mb-4">
                                    <svg className="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">Patient Management</h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Comprehensive patient records, medical history, and contact management in one place.
                                </p>
                            </div>

                            {/* Feature 2 */}
                            <div className="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div className="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg mb-4">
                                    <svg className="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">Appointment Scheduling</h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Easy appointment booking, calendar management, and automated reminders.
                                </p>
                            </div>

                            {/* Feature 3 */}
                            <div className="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div className="flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg mb-4">
                                    <svg className="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">Medical Records</h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Digital medical records, prescriptions, and lab results management.
                                </p>
                            </div>

                            {/* Feature 4 */}
                            <div className="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div className="flex items-center justify-center w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg mb-4">
                                    <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">Prescription Management</h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Digital prescription writing, drug interaction checks, and pharmacy integration.
                                </p>
                            </div>

                            {/* Feature 5 */}
                            <div className="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div className="flex items-center justify-center w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg mb-4">
                                    <svg className="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">Analytics & Reports</h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Comprehensive reporting, analytics dashboard, and performance insights.
                                </p>
                            </div>

                            {/* Feature 6 */}
                            <div className="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div className="flex items-center justify-center w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg mb-4">
                                    <svg className="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">Security & Compliance</h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    HIPAA compliant, encrypted data storage, and advanced security features.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Demo Video Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                See Medinext in Action
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                Watch how healthcare professionals use Medinext to streamline their practice
                            </p>
                        </div>

                        <div className="relative max-w-4xl mx-auto">
                            <div className="relative bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-1">
                                <div className="bg-white dark:bg-slate-900 rounded-xl overflow-hidden">
                                    {/* Video Placeholder */}
                                    <div className="relative aspect-video bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                        <div className="text-center">
                                            <div className="w-20 h-20 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 cursor-pointer hover:scale-110 transition-transform duration-300">
                                                <svg className="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </div>
                                            <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-2">
                                                Watch Demo Video
                                            </h3>
                                            <p className="text-slate-600 dark:text-slate-400">
                                                3-minute overview of Medinext features
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {/* Video Stats */}
                            <div className="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">3 min</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-300">Demo Duration</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-purple-600 dark:text-purple-400">50K+</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-300">Views</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-green-600 dark:text-green-400">4.9★</div>
                                    <div className="text-sm text-slate-600 dark:text-slate-300">Rating</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Customer Logos Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                Trusted by Leading Healthcare Organizations
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                Join thousands of healthcare providers who trust Medinext
                            </p>
                        </div>

                        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8 items-center opacity-60">
                            {/* Customer Logo Placeholders */}
                            <div className="flex items-center justify-center h-16 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <span className="text-slate-600 dark:text-slate-400 font-semibold text-sm">Metro Health</span>
                            </div>
                            <div className="flex items-center justify-center h-16 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <span className="text-slate-600 dark:text-slate-400 font-semibold text-sm">City Clinic</span>
                            </div>
                            <div className="flex items-center justify-center h-16 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <span className="text-slate-600 dark:text-slate-400 font-semibold text-sm">MedCenter</span>
                            </div>
                            <div className="flex items-center justify-center h-16 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <span className="text-slate-600 dark:text-slate-400 font-semibold text-sm">HealthPlus</span>
                            </div>
                            <div className="flex items-center justify-center h-16 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <span className="text-slate-600 dark:text-slate-400 font-semibold text-sm">CareGroup</span>
                            </div>
                            <div className="flex items-center justify-center h-16 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                <span className="text-slate-600 dark:text-slate-400 font-semibold text-sm">Wellness Co</span>
                            </div>
                        </div>
                    </div>

                    {/* Security & Compliance Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                Enterprise-Grade Security & Compliance
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                Your patient data is protected with industry-leading security measures
                            </p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            {/* HIPAA Compliance */}
                            <div className="text-center p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                                <div className="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">HIPAA Compliant</h3>
                                <p className="text-slate-600 dark:text-slate-300 text-sm">
                                    Full compliance with healthcare privacy regulations
                                </p>
                            </div>

                            {/* SOC 2 Type II */}
                            <div className="text-center p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                                <div className="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">SOC 2 Type II</h3>
                                <p className="text-slate-600 dark:text-slate-300 text-sm">
                                    Audited security controls and procedures
                                </p>
                            </div>

                            {/* 256-bit Encryption */}
                            <div className="text-center p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                                <div className="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">256-bit Encryption</h3>
                                <p className="text-slate-600 dark:text-slate-300 text-sm">
                                    Military-grade encryption for all data
                                </p>
                            </div>

                            {/* 99.9% Uptime */}
                            <div className="text-center p-6 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                                <div className="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-2">99.9% Uptime</h3>
                                <p className="text-slate-600 dark:text-slate-300 text-sm">
                                    Guaranteed availability and reliability
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Testimonials Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                Trusted by Healthcare Professionals
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                See what our users say about Medinext
                            </p>
                        </div>

                        <div className="relative max-w-4xl mx-auto">
                            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 sm:p-12">
                                <div className="text-center">
                                    {/* Star Rating */}
                                    <div className="flex justify-center mb-6">
                                        {[...Array(testimonials[currentTestimonial].rating)].map((_, i) => (
                                            <svg key={i} className="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        ))}
                                    </div>

                                    {/* Testimonial Content */}
                                    <blockquote className="text-xl text-slate-700 dark:text-slate-300 mb-6">
                                        "{testimonials[currentTestimonial].content}"
                                    </blockquote>

                                    {/* Author Info */}
                                    <div className="flex items-center justify-center">
                                        <div className="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                            {testimonials[currentTestimonial].name.split(' ').map(n => n[0]).join('')}
                                        </div>
                                        <div className="text-left">
                                            <div className="font-semibold text-slate-900 dark:text-white">
                                                {testimonials[currentTestimonial].name}
                                            </div>
                                            <div className="text-sm text-slate-600 dark:text-slate-400">
                                                {testimonials[currentTestimonial].role}, {testimonials[currentTestimonial].organization}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Testimonial Navigation Dots */}
                            <div className="flex justify-center mt-6 space-x-2">
                                {testimonials.map((_, index) => (
                                    <button
                                        key={index}
                                        onClick={() => setCurrentTestimonial(index)}
                                        className={`w-3 h-3 rounded-full transition-all duration-300 ${
                                            index === currentTestimonial 
                                                ? 'bg-blue-600 w-8' 
                                                : 'bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 dark:hover:bg-slate-500'
                                        }`}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Pricing Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                Simple, Transparent Pricing
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                Choose the plan that fits your practice
                            </p>
                        </div>

                        <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                            {/* Starter Plan */}
                            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8 border border-slate-200 dark:border-slate-700">
                                <div className="text-center">
                                    <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-2">Starter</h3>
                                    <div className="text-4xl font-bold text-slate-900 dark:text-white mb-1">$29</div>
                                    <div className="text-slate-600 dark:text-slate-400 mb-6">per month</div>
                                    <ul className="space-y-3 text-left">
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Up to 1,000 patients
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Basic appointment scheduling
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Digital prescriptions
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Email support
                                        </li>
                                    </ul>
                                    <Link
                                        href={register()}
                                        className="mt-6 w-full inline-flex justify-center items-center px-6 py-3 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                                    >
                                        Get Started
                                    </Link>
                                </div>
                            </div>

                            {/* Professional Plan */}
                            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border-2 border-blue-600 relative">
                                <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                    <span className="bg-blue-600 text-white px-4 py-1 rounded-full text-sm font-medium">Most Popular</span>
                                </div>
                                <div className="text-center">
                                    <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-2">Professional</h3>
                                    <div className="text-4xl font-bold text-slate-900 dark:text-white mb-1">$79</div>
                                    <div className="text-slate-600 dark:text-slate-400 mb-6">per month</div>
                                    <ul className="space-y-3 text-left">
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Up to 5,000 patients
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Advanced scheduling
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Analytics & reports
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Priority support
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            API access
                                        </li>
                                    </ul>
                                    <Link
                                        href={register()}
                                        className="mt-6 w-full inline-flex justify-center items-center px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200"
                                    >
                                        Get Started
                                    </Link>
                                </div>
                            </div>

                            {/* Enterprise Plan */}
                            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8 border border-slate-200 dark:border-slate-700">
                                <div className="text-center">
                                    <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-2">Enterprise</h3>
                                    <div className="text-4xl font-bold text-slate-900 dark:text-white mb-1">$199</div>
                                    <div className="text-slate-600 dark:text-slate-400 mb-6">per month</div>
                                    <ul className="space-y-3 text-left">
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Unlimited patients
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Multi-clinic support
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Custom integrations
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            24/7 phone support
                                        </li>
                                        <li className="flex items-center">
                                            <svg className="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            Dedicated account manager
                                        </li>
                                    </ul>
                                    <Link
                                        href={register()}
                                        className="mt-6 w-full inline-flex justify-center items-center px-6 py-3 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                                    >
                                        Contact Sales
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* FAQ Section */}
                    <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                Frequently Asked Questions
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                Everything you need to know about Medinext
                            </p>
                        </div>

                        <div className="space-y-6">
                            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-3">
                                    Is Medinext HIPAA compliant?
                                </h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Yes, Medinext is fully HIPAA compliant. We implement industry-standard security measures including encryption, access controls, and regular security audits to protect patient data.
                                </p>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-3">
                                    Can I migrate my existing patient data?
                                </h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Absolutely! Our team provides free data migration services to help you transfer your existing patient records, appointments, and other data from your current system.
                                </p>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-3">
                                    Do you offer training and support?
                                </h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Yes, we provide comprehensive training for all users and offer multiple support channels including email, phone, and live chat depending on your plan.
                                </p>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-3">
                                    Can I try Medinext before purchasing?
                                </h3>
                                <p className="text-slate-600 dark:text-slate-300">
                                    Yes! We offer a 30-day free trial with full access to all features. No credit card required to get started.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Integrations Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                Seamless Integrations
                            </h2>
                            <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                Connect with the tools and systems you already use
                            </p>
                        </div>

                        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                            {/* Integration Cards */}
                            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-sm border border-slate-200 dark:border-slate-700 text-center hover:shadow-md transition-shadow duration-300">
                                <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg className="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h3 className="text-sm font-medium text-slate-900 dark:text-white">Lab Systems</h3>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-sm border border-slate-200 dark:border-slate-700 text-center hover:shadow-md transition-shadow duration-300">
                                <div className="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg className="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </div>
                                <h3 className="text-sm font-medium text-slate-900 dark:text-white">Pharmacy</h3>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-sm border border-slate-200 dark:border-slate-700 text-center hover:shadow-md transition-shadow duration-300">
                                <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg className="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-sm font-medium text-slate-900 dark:text-white">Billing</h3>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-sm border border-slate-200 dark:border-slate-700 text-center hover:shadow-md transition-shadow duration-300">
                                <div className="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg className="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-sm font-medium text-slate-900 dark:text-white">Insurance</h3>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-sm border border-slate-200 dark:border-slate-700 text-center hover:shadow-md transition-shadow duration-300">
                                <div className="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg className="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 className="text-sm font-medium text-slate-900 dark:text-white">Mobile Apps</h3>
                            </div>

                            <div className="bg-white dark:bg-slate-800 rounded-lg p-6 shadow-sm border border-slate-200 dark:border-slate-700 text-center hover:shadow-md transition-shadow duration-300">
                                <div className="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <svg className="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-sm font-medium text-slate-900 dark:text-white">API Access</h3>
                            </div>
                        </div>

                        <div className="text-center mt-8">
                            <p className="text-slate-600 dark:text-slate-400 mb-4">
                                Need a custom integration? Our team can help.
                            </p>
                            <Link
                                href="#"
                                className="inline-flex items-center px-6 py-3 text-sm font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200"
                            >
                                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                View All Integrations
                            </Link>
                        </div>
                    </div>

                    {/* Contact Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 sm:p-12">
                            <div className="text-center mb-12">
                                <h2 className="text-3xl font-bold text-slate-900 dark:text-white sm:text-4xl">
                                    Get in Touch
                                </h2>
                                <p className="mt-4 text-lg text-slate-600 dark:text-slate-300">
                                    Ready to transform your healthcare practice? Let's talk.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
                                {/* Contact Information */}
                                <div>
                                    <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-6">
                                        Contact Information
                                    </h3>
                                    
                                    <div className="space-y-6">
                                        <div className="flex items-start">
                                            <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                                                <svg className="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 className="font-medium text-slate-900 dark:text-white">Email</h4>
                                                <p className="text-slate-600 dark:text-slate-400">support@medinext.com</p>
                                                <p className="text-slate-600 dark:text-slate-400">sales@medinext.com</p>
                                            </div>
                                        </div>

                                        <div className="flex items-start">
                                            <div className="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                                                <svg className="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 className="font-medium text-slate-900 dark:text-white">Phone</h4>
                                                <p className="text-slate-600 dark:text-slate-400">+1 (555) 123-4567</p>
                                                <p className="text-slate-600 dark:text-slate-400">Mon-Fri 8AM-6PM EST</p>
                                            </div>
                                        </div>

                                        <div className="flex items-start">
                                            <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                                                <svg className="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 className="font-medium text-slate-900 dark:text-white">Office</h4>
                                                <p className="text-slate-600 dark:text-slate-400">123 Healthcare Ave</p>
                                                <p className="text-slate-600 dark:text-slate-400">Medical District, NY 10001</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-8">
                                        <h4 className="font-medium text-slate-900 dark:text-white mb-4">Support Hours</h4>
                                        <div className="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                                            <div className="flex justify-between">
                                                <span>Monday - Friday</span>
                                                <span>8:00 AM - 6:00 PM EST</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span>Saturday</span>
                                                <span>9:00 AM - 2:00 PM EST</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span>Sunday</span>
                                                <span>Emergency Support Only</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Contact Form */}
                                <div>
                                    <h3 className="text-xl font-semibold text-slate-900 dark:text-white mb-6">
                                        Send us a Message
                                    </h3>
                                    
                                    <form className="space-y-6">
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                            <div>
                                                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                    First Name
                                                </label>
                                                <input
                                                    type="text"
                                                    className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                                    placeholder="John"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                    Last Name
                                                </label>
                                                <input
                                                    type="text"
                                                    className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                                    placeholder="Doe"
                                                />
                                            </div>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                Email
                                            </label>
                                            <input
                                                type="email"
                                                className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                                placeholder="john@example.com"
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                Organization
                                            </label>
                                            <input
                                                type="text"
                                                className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                                placeholder="Your Healthcare Organization"
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                Message
                                            </label>
                                            <textarea
                                                rows={4}
                                                className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                                placeholder="Tell us about your needs..."
                                            ></textarea>
                                        </div>

                                        <button
                                            type="submit"
                                            className="w-full inline-flex justify-center items-center px-6 py-3 text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                                        >
                                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            Send Message
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Newsletter Signup */}
                    <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 sm:p-12 text-center text-white">
                            <h2 className="text-3xl font-bold mb-4">Stay Updated</h2>
                            <p className="text-xl mb-8 opacity-90">
                                Get the latest healthcare technology insights and Medinext updates
                            </p>
                            <div className="max-w-md mx-auto flex flex-col sm:flex-row gap-4">
                                <input
                                    type="email"
                                    placeholder="Enter your email"
                                    className="flex-1 px-4 py-3 rounded-lg text-slate-900 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-white/50"
                                />
                                <button className="px-6 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200">
                                    Subscribe
                                </button>
                            </div>
                            <p className="text-sm opacity-75 mt-4">
                                We respect your privacy. Unsubscribe at any time.
                            </p>
                        </div>
                    </div>

                    {/* Quick Start Section */}
                    <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                        <div className="rounded-3xl bg-gradient-to-r from-blue-600 to-purple-600 p-8 sm:p-12 text-center text-white">
                            <h2 className="text-3xl font-bold mb-4">Ready to get started?</h2>
                            <p className="text-xl mb-8 opacity-90">
                                Join thousands of healthcare professionals using Medinext
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                {!auth.user && (
                                    <>
                                        <Link
                                            href={register()}
                                            className="inline-flex items-center px-8 py-3 text-base font-medium text-blue-600 bg-white rounded-lg hover:bg-gray-50 transition-colors duration-200"
                                        >
                                            Start Free Trial
                                        </Link>
                                        <Link
                                            href="https://laravel.com/docs"
                                            target="_blank"
                                            className="inline-flex items-center px-8 py-3 text-base font-medium text-white border border-white/30 rounded-lg hover:bg-white/10 transition-colors duration-200"
                                        >
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            View Documentation
                                        </Link>
                                    </>
                                )}
                                {auth.user && (
                                    <Link
                                        href={dashboard()}
                                        className="inline-flex items-center px-8 py-3 text-base font-medium text-blue-600 bg-white rounded-lg hover:bg-gray-50 transition-colors duration-200"
                                    >
                                        Access Dashboard
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                </main>

                {/* Footer */}
                <footer className="bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                    <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                            {/* Company Info */}
                            <div className="col-span-1 md:col-span-2">
                                <div className="flex items-center space-x-2 mb-4">
                                    <div className="h-8 w-8 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                        <span className="text-white font-bold text-sm">M</span>
                                    </div>
                                    <span className="text-xl font-bold text-slate-900 dark:text-white">Medinext</span>
                                </div>
                                <p className="text-slate-600 dark:text-slate-400 mb-4 max-w-md">
                                    The most comprehensive Electronic Medical Records solution for modern healthcare. 
                                    Streamline patient care with cutting-edge technology.
                                </p>
                                <div className="flex space-x-4">
                                    <a href="#" className="text-slate-400 hover:text-blue-600 transition-colors duration-200">
                                        <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                        </svg>
                                    </a>
                                    <a href="#" className="text-slate-400 hover:text-blue-600 transition-colors duration-200">
                                        <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                        </svg>
                                    </a>
                                    <a href="#" className="text-slate-400 hover:text-blue-600 transition-colors duration-200">
                                        <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {/* Quick Links */}
                            <div>
                                <h3 className="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-4">
                                    Product
                                </h3>
                                <ul className="space-y-3">
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Features</a></li>
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Pricing</a></li>
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Security</a></li>
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Integrations</a></li>
                                </ul>
                            </div>

                            {/* Support */}
                            <div>
                                <h3 className="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-4">
                                    Support
                                </h3>
                                <ul className="space-y-3">
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Documentation</a></li>
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Help Center</a></li>
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Contact Us</a></li>
                                    <li><a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200">Status</a></li>
                                </ul>
                            </div>
                        </div>

                        <div className="mt-8 pt-8 border-t border-slate-200 dark:border-slate-700">
                            <div className="flex flex-col md:flex-row justify-between items-center">
                                <div className="text-slate-600 dark:text-slate-400 text-sm">
                                    &copy; 2024 Medinext. All rights reserved. Built with Laravel and modern web technologies.
                                </div>
                                <div className="flex space-x-6 mt-4 md:mt-0">
                                    <a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200 text-sm">Privacy Policy</a>
                                    <a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200 text-sm">Terms of Service</a>
                                    <a href="#" className="text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors duration-200 text-sm">Cookie Policy</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
