<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

/* -------------------------------------------------------------------------- */
/* TYPES */
/* -------------------------------------------------------------------------- */

interface SupplierOrder {
    id: number
    number: string
    order_date: string
    supplier: { id: number; name: string }
    client_order: { id: number; number: string }
    total_amount: number
    status: string
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { orders } = defineProps<{
    orders: SupplierOrder[]
}>()

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function destroy(order: SupplierOrder) {
    if (confirm(`Tem certeza que deseja eliminar a encomenda ${order.number}?`)) {
        router.delete(`/supplier-orders/${order.id}`, {
            preserveScroll: true,
        })
    }
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('pt-PT')
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(price)
}

function getStatusClass(status: string): string {
    return status === 'closed'
        ? 'bg-green-100 text-green-800'
        : 'bg-gray-100 text-gray-800'
}

function getStatusLabel(status: string): string {
    return status === 'closed' ? 'Fechado' : 'Rascunho'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Encomendas - Fornecedores
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Encomendas de Fornecedores</CardTitle>
                        <p class="text-sm text-muted-foreground mt-2">
                            Estas encomendas são geradas automaticamente a partir de Encomendas de Cliente fechadas.
                        </p>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Número</TableHead>
                                    <TableHead>Data</TableHead>
                                    <TableHead>Fornecedor</TableHead>
                                    <TableHead>Encomenda Cliente</TableHead>
                                    <TableHead>Valor Total</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="order in orders" :key="order.id">
                                    <TableCell class="font-medium">
                                        {{ order.number }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(order.order_date) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ order.supplier.name }}
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-xs text-blue-600">
                                            {{ order.client_order.number }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ formatPrice(order.total_amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <span :class="[
                                            'rounded-full px-2 py-1 text-xs',
                                            getStatusClass(order.status)
                                        ]">
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="destructive" @click="destroy(order)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="orders.length === 0">
                                    <TableCell colspan="7" class="text-center text-muted-foreground">
                                        Nenhuma encomenda de fornecedor encontrada.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>