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

interface Proposal {
    id: number
    number: string
    proposal_date?: string
    validity_date: string
    client: { id: number; name: string }
    total_amount: number
    status: string
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { proposals } = defineProps<{
    proposals: Proposal[]
}>()

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function goToCreate() {
    router.visit('/proposals/create')
}

function goToEdit(id: number) {
    router.visit(`/proposals/${id}/edit`)
}

function destroy(proposal: Proposal) {
    if (confirm(`Tem certeza que deseja eliminar a proposta ${proposal.number}?`)) {
        router.delete(`/proposals/${proposal.id}`, {
            preserveScroll: true,
        })
    }
}

function convertToOrder(proposalId: number) {
    if (confirm('Converter esta proposta em encomenda?')) {
        router.post(`/proposals/${proposalId}/convert-to-order`, {}, {
            onSuccess: () => {
                alert('Proposta convertida com sucesso!')
                router.visit('/client-orders')
            },
            preserveScroll: true,
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

function downloadPdf(id: number) {
    window.open(`/proposals/${id}/pdf`, '_blank')
}

</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Propostas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Lista de Propostas</CardTitle>
                        <Button @click="goToCreate">
                            + Nova Proposta
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Número</TableHead>
                                    <TableHead>Data</TableHead>
                                    <TableHead>Validade</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Valor Total</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="proposal in proposals" :key="proposal.id">
                                    <TableCell class="font-medium">
                                        {{ proposal.number }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(proposal.proposal_date) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(proposal.validity_date) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ proposal.client.name }}
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ formatPrice(proposal.total_amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <span :class="[
                                            'rounded-full px-2 py-1 text-xs',
                                            getStatusClass(proposal.status)
                                        ]">
                                            {{ getStatusLabel(proposal.status) }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="downloadPdf(proposal.id)">
                                                PDF
                                            </Button>
                                            <Button v-if="proposal.status === 'closed'" size="sm" variant="default"
                                                @click="convertToOrder(proposal.id)">
                                                Encomenda
                                            </Button>

                                            <Button size="sm" variant="outline" @click="goToEdit(proposal.id)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(proposal)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="proposals.length === 0">
                                    <TableCell colspan="7" class="text-center text-muted-foreground">
                                        Nenhuma proposta encontrada.
                                        <Button variant="link" @click="goToCreate" class="ml-2">
                                            Criar a primeira proposta
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