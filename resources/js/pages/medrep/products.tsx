import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Plus,
    Search,
    Edit,
    Trash2,
    Eye,
    Filter,
    DollarSign,
    TrendingUp,
    Building2,
    Shield
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Product Management',
        href: '/medrep/products',
    },
];

interface ProductManagementProps {
    user?: {
        id: number;
        name: string;
        email: string;
        role: string;
        company_id?: number;
        company?: {
            id: number;
            name: string;
        };
    };
}

export default function ProductManagement({ user }: ProductManagementProps = {}) {
    const products = [
        {
            id: 1,
            name: 'CardioMax 10mg',
            category: 'Cardiology',
            manufacturer: 'MedPharma Inc.',
            price: 45.99,
            stock: 150,
            status: 'Active',
            lastOrder: '2024-01-10',
            sales: 25
        },
        {
            id: 2,
            name: 'NeuroCalm 5mg',
            category: 'Neurology',
            manufacturer: 'NeuroMed Corp.',
            price: 32.50,
            stock: 89,
            status: 'Active',
            lastOrder: '2024-01-12',
            sales: 18
        },
        {
            id: 3,
            name: 'DermaCare Cream',
            category: 'Dermatology',
            manufacturer: 'SkinHealth Ltd.',
            price: 28.75,
            stock: 0,
            status: 'Out of Stock',
            lastOrder: '2024-01-08',
            sales: 12
        },
        {
            id: 4,
            name: 'OrthoFlex 500mg',
            category: 'Orthopedics',
            manufacturer: 'BoneMed Inc.',
            price: 55.00,
            stock: 75,
            status: 'Active',
            lastOrder: '2024-01-15',
            sales: 8
        }
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'Active': return 'default';
            case 'Out of Stock': return 'destructive';
            case 'Discontinued': return 'secondary';
            default: return 'secondary';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Product Management - Medinext">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                    {/* Modern Header */}
                    <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white shadow-xl">
                        <div className="absolute inset-0 bg-black/10"></div>
                        <div className="relative flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight">
                                    Product Management
                                </h1>
                                <p className="mt-2 text-emerald-100">
                                    Manage your product portfolio and inventory
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                    <Shield className="h-3 w-3" />
                                    Medical Rep
                                </Badge>
                                {user?.company && (
                                    <Badge variant="secondary" className="flex items-center gap-1 bg-white/20 text-white border-white/30 hover:bg-white/30">
                                        <Building2 className="h-3 w-3" />
                                        {user.company.name}
                                    </Badge>
                                )}
                            </div>
                        </div>
                        {/* Decorative elements */}
                        <div className="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div className="absolute -bottom-2 -left-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>

                    {/* Action Button */}
                    <div className="flex justify-end">
                        <Button className="bg-emerald-600 hover:bg-emerald-700 text-white">
                            <Plus className="mr-2 h-4 w-4" />
                            Add Product
                        </Button>
                    </div>

                    <Card className="border-0 shadow-lg bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg font-semibold text-slate-900 dark:text-white">Product Portfolio</CardTitle>
                            <CardDescription className="text-slate-600 dark:text-slate-300">
                                View and manage all products in your portfolio
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="relative flex-1">
                                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-slate-400" />
                                    <Input placeholder="Search products..." className="pl-8 border-slate-200 dark:border-slate-700" />
                                </div>
                                <Button variant="outline" className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <Filter className="mr-2 h-4 w-4" />
                                    Filter
                                </Button>
                            </div>

                            <Table>
                                <TableHeader>
                                    <TableRow className="border-slate-200 dark:border-slate-700">
                                        <TableHead className="text-slate-700 dark:text-slate-300">Product</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Category</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Manufacturer</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Price</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Stock</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Sales</TableHead>
                                        <TableHead className="text-slate-700 dark:text-slate-300">Status</TableHead>
                                        <TableHead className="text-right text-slate-700 dark:text-slate-300">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {products.map((product) => (
                                        <TableRow key={product.id} className="border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <TableCell>
                                                <div>
                                                    <div className="font-medium text-slate-900 dark:text-white">{product.name}</div>
                                                    <div className="text-sm text-slate-500 dark:text-slate-400">ID: {product.id}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className="border-slate-200 dark:border-slate-700">{product.category}</Badge>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{product.manufacturer}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-slate-900 dark:text-white">
                                                    <DollarSign className="mr-1 h-3 w-3" />
                                                    {product.price.toFixed(2)}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-slate-900 dark:text-white">{product.stock}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center text-slate-900 dark:text-white">
                                                    <TrendingUp className="mr-1 h-3 w-3" />
                                                    {product.sales}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={getStatusColor(product.status)} className="border-0">
                                                    {product.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end space-x-2">
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="sm" className="hover:bg-slate-100 dark:hover:bg-slate-700">
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
