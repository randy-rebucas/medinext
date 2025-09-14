import { Head, Link, useForm } from '@inertiajs/react';
import { Users, ArrowRight, ArrowLeft, UserPlus, Mail, UserCheck, Building2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Progress } from '@/components/ui/progress';
import InputError from '@/components/input-error';

interface TeamSetupProps {
    user: {
        id: number;
        name: string;
        email: string;
    };
    clinic: {
        id: number;
        name: string;
    } | null;
    roles: Array<{
        id: number;
        name: string;
        description: string;
    }>;
}

export default function TeamSetup({ user, clinic, roles }: TeamSetupProps) {
    const { data, setData, post, processing, errors } = useForm({
        team_members: [
            {
                name: '',
                email: '',
                role_id: '',
                department: '',
            }
        ],
    });

    const addTeamMember = () => {
        setData('team_members', [
            ...data.team_members,
            {
                name: '',
                email: '',
                role_id: '',
                department: '',
            }
        ]);
    };

    const removeTeamMember = (index: number) => {
        if (data.team_members.length > 1) {
            const updated = data.team_members.filter((_, i) => i !== index);
            setData('team_members', updated);
        }
    };

    const updateTeamMember = (index: number, field: string, value: string) => {
        const updated = [...data.team_members];
        updated[index] = { ...updated[index], [field]: value };
        setData('team_members', updated);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/onboarding/team-setup');
    };

    const handleSkip = () => {
        // Skip team setup and go to completion
        window.location.href = '/onboarding/complete';
    };

    return (
        <>
            <Head title="Team Setup - Medinext" />
            
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="container mx-auto px-4 py-8">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <div className="flex items-center justify-center space-x-2 mb-4">
                            <div className="h-12 w-12 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                <span className="text-white font-bold text-xl">M</span>
                            </div>
                            <span className="text-3xl font-bold text-slate-900 dark:text-white">Medinext</span>
                        </div>
                        <h1 className="text-4xl font-bold text-slate-900 dark:text-white mb-4">
                            Set Up Your Team
                        </h1>
                        <p className="text-xl text-slate-600 dark:text-slate-300 mb-6">
                            Add team members to your clinic (optional)
                        </p>
                    </div>

                    {/* Progress */}
                    <div className="max-w-2xl mx-auto mb-8">
                        <div className="flex items-center justify-between mb-2">
                            <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Setup Progress</span>
                            <span className="text-sm text-slate-500 dark:text-slate-400">Step 3 of 4</span>
                        </div>
                        <Progress value={75} className="h-2" />
                    </div>

                    {/* Clinic Info */}
                    {clinic && (
                        <div className="max-w-2xl mx-auto mb-8">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center">
                                        <Building2 className="h-5 w-5 mr-2" />
                                        {clinic.name}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-slate-600 dark:text-slate-300">
                                        Add team members to help manage your clinic operations.
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    )}

                    {/* Team Setup Form */}
                    <div className="max-w-4xl mx-auto">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Users className="h-5 w-5 mr-2" />
                                    Team Members
                                </CardTitle>
                                <CardDescription>
                                    Add staff members who will help manage your clinic. You can always add more later.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    {data.team_members.map((member, index) => (
                                        <Card key={index} className="border border-slate-200 dark:border-slate-700">
                                            <CardContent className="p-6">
                                                <div className="flex items-center justify-between mb-4">
                                                    <h4 className="font-medium text-slate-900 dark:text-white">
                                                        Team Member {index + 1}
                                                    </h4>
                                                    {data.team_members.length > 1 && (
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => removeTeamMember(index)}
                                                            className="text-red-600 hover:text-red-700"
                                                        >
                                                            Remove
                                                        </Button>
                                                    )}
                                                </div>

                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <Label htmlFor={`name_${index}`}>Full Name</Label>
                                                        <Input
                                                            id={`name_${index}`}
                                                            type="text"
                                                            value={member.name}
                                                            onChange={(e) => updateTeamMember(index, 'name', e.target.value)}
                                                            placeholder="Enter full name"
                                                        />
                                                        <InputError message={errors[`team_members.${index}.name`]} />
                                                    </div>

                                                    <div>
                                                        <Label htmlFor={`email_${index}`}>Email Address</Label>
                                                        <Input
                                                            id={`email_${index}`}
                                                            type="email"
                                                            value={member.email}
                                                            onChange={(e) => updateTeamMember(index, 'email', e.target.value)}
                                                            placeholder="Enter email address"
                                                        />
                                                        <InputError message={errors[`team_members.${index}.email`]} />
                                                    </div>

                                                    <div>
                                                        <Label htmlFor={`role_${index}`}>Role</Label>
                                                        <Select
                                                            value={member.role_id}
                                                            onValueChange={(value) => updateTeamMember(index, 'role_id', value)}
                                                        >
                                                            <SelectTrigger>
                                                                <SelectValue placeholder="Select a role" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                {roles.map((role) => (
                                                                    <SelectItem key={role.id} value={role.id.toString()}>
                                                                        {role.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectContent>
                                                        </Select>
                                                        <InputError message={errors[`team_members.${index}.role_id`]} />
                                                    </div>

                                                    <div>
                                                        <Label htmlFor={`department_${index}`}>Department</Label>
                                                        <Input
                                                            id={`department_${index}`}
                                                            type="text"
                                                            value={member.department}
                                                            onChange={(e) => updateTeamMember(index, 'department', e.target.value)}
                                                            placeholder="e.g., Administration, Medical, Reception"
                                                        />
                                                        <InputError message={errors[`team_members.${index}.department`]} />
                                                    </div>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    ))}

                                    <div className="flex justify-center">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={addTeamMember}
                                            className="flex items-center"
                                        >
                                            <UserPlus className="h-4 w-4 mr-2" />
                                            Add Another Team Member
                                        </Button>
                                    </div>

                                    <div className="flex flex-col sm:flex-row gap-4 pt-6">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            asChild
                                            className="flex-1"
                                        >
                                            <Link href="/onboarding/clinic-setup">
                                                <ArrowLeft className="h-4 w-4 mr-2" />
                                                Back to Clinic Setup
                                            </Link>
                                        </Button>

                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={handleSkip}
                                            className="flex-1"
                                        >
                                            Skip Team Setup
                                        </Button>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700"
                                        >
                                            {processing ? 'Adding Team Members...' : 'Add Team Members'}
                                            <ArrowRight className="h-4 w-4 ml-2" />
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Help Text */}
                    <div className="max-w-2xl mx-auto mt-8">
                        <Card>
                            <CardContent className="p-6">
                                <div className="flex items-start space-x-3">
                                    <UserCheck className="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                                    <div>
                                        <h4 className="font-medium text-slate-900 dark:text-white mb-2">
                                            Team Management Tips
                                        </h4>
                                        <ul className="text-sm text-slate-600 dark:text-slate-300 space-y-1">
                                            <li>• You can add team members later from the admin panel</li>
                                            <li>• Each team member will receive an email invitation</li>
                                            <li>• Roles determine what features each member can access</li>
                                            <li>• You can change roles and permissions anytime</li>
                                        </ul>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
