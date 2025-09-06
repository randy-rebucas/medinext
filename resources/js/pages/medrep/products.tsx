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
    Package, 
    Plus, 
    Search, 
    Edit, 
    Trash2, 
    Eye,
    Filter,
    Calendar,
    DollarSign,
    TrendingUp
} from 'lucide-react';

export default function ProductManagement() {
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
        <AppLayout>
            <Head title="Product Management" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Product Management</h1>
                        <p className="text-muted-foreground">
                            Manage your product portfolio and inventory
                        </p>
                    </div>
                    <Button>
                        <Plus className="mr-2 h-4 w-4" />
                        Add Product
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Product Portfolio</CardTitle>
                        <CardDescription>
                            View and manage all products in your portfolio
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-2 mb-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Search products..." className="pl-8" />
                            </div>
                            <Button variant="outline">
                                <Filter className="mr-2 h-4 w-4" />
                                Filter
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Manufacturer</TableHead>
                                    <TableHead>Price</TableHead>
                                    <TableHead>Stock</TableHead>
                                    <TableHead>Sales</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {products.map((product) => (
                                    <TableRow key={product.id}>
                                        <TableCell>
                                            <div>
                                                <div className="font-medium">{product.name}</div>
                                                <div className="text-sm text-muted-foreground">ID: {product.id}</div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{product.category}</Badge>
                                        </TableCell>
                                        <TableCell>{product.manufacturer}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <DollarSign className="mr-1 h-3 w-3" />
                                                {product.price.toFixed(2)}
                                            </div>
                                        </TableCell>
                                        <TableCell>{product.stock}</TableCell>
                                        <TableCell>
                                            <div className="flex items-center">
                                                <TrendingUp className="mr-1 h-3 w-3" />
                                                {product.sales}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={getStatusColor(product.status)}>
                                                {product.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex items-center justify-end space-x-2">
                                                <Button variant="ghost" size="sm">
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm">
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm">
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
        </AppLayout>
    );
}
