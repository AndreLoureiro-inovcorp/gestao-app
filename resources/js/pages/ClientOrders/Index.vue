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

interface ClientOrder {
    id: number
    number: string
    order_date?: string
    client: { id: number; name: string }
    proposal?: { id: number; number: string }
    total_amount: number
    status: string
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { orders } = defineProps<{
    orders: ClientOrder[]
}>()

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function goToCreate() {
    router.visit('/client-orders/create')
}

function goToEdit(id: number) {
    router.visit(`/client-orders/${id}/edit`)
}

function destroy(order: ClientOrder) {
    if (confirm(`Tem certeza que deseja eliminar a encomenda ${order.number}?`)) {
        router.delete(`/client-orders/${order.id}`, {
            preserveScroll: true,
        })
    }
}

function createSupplierOrders(orderId: number) {
    if (confirm('Criar encomendas de fornecedor a partir desta encomenda?\n\nSerá criada uma encomenda para cada fornecedor associado aos artigos.')) {
        router.post(`/client-orders/${orderId}/create-supplier-orders`, {}, {
            onSuccess: () => {
                alert('Encomendas de fornecedor criadas com sucesso!')
                router.visit('/supplier-orders')
            }
        })
    }
}

function formatDate(date: string | undefined) {
    if (!date) return '-'
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
                Encomendas - Clientes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Lista de Encomendas</CardTitle>
                        <Button @click="goToCreate">
                            + Nova Encomenda
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Número</TableHead>
                                    <TableHead>Data</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Proposta</TableHead>
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
                                        {{ order.client.name }}
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="order.proposal" class="text-xs text-blue-600">
                                            {{ order.proposal.number }}
                                        </span>
                                        <span v-else class="text-xs text-gray-400">
                                            Manual
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
                                            <!-- Criar Encomendas Fornecedor (só se fechado) -->
                                            <Button v-if="order.status === 'closed'" size="sm" variant="default"
                                                @click="createSupplierOrders(order.id)">
                                                → Fornecedores
                                            </Button>

                                            <Button size="sm" variant="outline" @click="goToEdit(order.id)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(order)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="orders.length === 0">
                                    <TableCell colspan="7" class="text-center text-muted-foreground">
                                        Nenhuma encomenda encontrada.
                                        <Button variant="link" @click="goToCreate" class="ml-2">
                                            Criar a primeira encomenda
                                        </Button>
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