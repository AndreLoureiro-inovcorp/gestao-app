<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

/* -------------------------------------------------------------------------- */
/* TYPES */
/* -------------------------------------------------------------------------- */

interface SupplierInvoice {
    id: number
    number: string
    invoice_date: string
    due_date: string
    supplier: { id: number; name: string; email?: string }
    supplier_order?: { id: number; number: string }
    total_amount: number
    document_path: string | null
    payment_proof_path: string | null
    status: string
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { invoices } = defineProps<{
    invoices: SupplierInvoice[]
}>()

/* -------------------------------------------------------------------------- */
/* STATE */
/* -------------------------------------------------------------------------- */

const showPaymentModal = ref(false)
const selectedInvoice = ref<SupplierInvoice | null>(null)
const paymentProofFile = ref<File | null>(null)

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function goToCreate() {
    router.visit('/supplier-invoices/create')
}

function destroy(invoice: SupplierInvoice) {
    if (confirm(`Tem certeza que deseja eliminar a fatura ${invoice.number}?`)) {
        router.delete(`/supplier-invoices/${invoice.id}`, {
            preserveScroll: true,
        })
    }
}

function downloadDocument(path: string) {
    window.open(`/storage/${path}`, '_blank')
}

function openPaymentModal(invoice: SupplierInvoice) {
    selectedInvoice.value = invoice
    paymentProofFile.value = null
    showPaymentModal.value = true
}

function onPaymentProofChange(event: Event) {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        paymentProofFile.value = target.files[0]
    }
}

function sendPaymentNotification() {
    if (!selectedInvoice.value || !paymentProofFile.value) {
        alert('Por favor, selecione o comprovativo de pagamento!')
        return
    }

    const formData = new FormData()
    formData.append('payment_proof', paymentProofFile.value)

    router.post(`/supplier-invoices/${selectedInvoice.value.id}/send-payment-notification`, formData, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false
            selectedInvoice.value = null
            paymentProofFile.value = null
        },
    })
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
    return status === 'paid'
        ? 'bg-green-100 text-green-800'
        : 'bg-yellow-100 text-yellow-800'
}

function getStatusLabel(status: string): string {
    return status === 'paid' ? 'Paga' : 'Pendente'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Faturas Fornecedor
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Lista de Faturas</CardTitle>
                        <Button @click="goToCreate">
                            Nova Fatura
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Número</TableHead>
                                    <TableHead>Data</TableHead>
                                    <TableHead>Vencimento</TableHead>
                                    <TableHead>Fornecedor</TableHead>
                                    <TableHead>Encomenda</TableHead>
                                    <TableHead>Documento</TableHead>
                                    <TableHead>Valor Total</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="invoice in invoices" :key="invoice.id">
                                    <TableCell class="font-medium">
                                        {{ invoice.number }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(invoice.invoice_date) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDate(invoice.due_date) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ invoice.supplier.name }}
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="invoice.supplier_order" class="text-xs text-blue-600">
                                            {{ invoice.supplier_order.number }}
                                        </span>
                                        <span v-else class="text-xs text-gray-400">
                                            -
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <Button v-if="invoice.document_path" size="sm" variant="ghost"
                                            @click="downloadDocument(invoice.document_path)">
                                            Ver
                                        </Button>
                                        <span v-else class="text-xs text-gray-400">-</span>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ formatPrice(invoice.total_amount) }}
                                    </TableCell>
                                    <TableCell>
                                        <span :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            getStatusClass(invoice.status)
                                        ]">
                                            {{ getStatusLabel(invoice.status) }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">

                                            <Button v-if="invoice.status === 'paid'" size="sm" variant="default"
                                                @click="openPaymentModal(invoice)">
                                                Enviar Comprovativo
                                            </Button>

                                            <Button size="sm" variant="destructive" @click="destroy(invoice)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="invoices.length === 0">
                                    <TableCell colspan="9" class="text-center text-muted-foreground">
                                        Nenhuma fatura encontrada.
                                        <Button variant="link" @click="goToCreate" class="ml-2">
                                            Criar a primeira fatura
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

            </div>
        </div>

        <Dialog v-model:open="showPaymentModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Enviar Comprovativo ao Fornecedor</DialogTitle>
                    <DialogDescription>
                        <div class="space-y-1">
                            <p><strong>Fatura:</strong> {{ selectedInvoice?.number }}</p>
                            <p><strong>Fornecedor:</strong> {{ selectedInvoice?.supplier.name }}</p>
                            <p><strong>Valor:</strong> {{ selectedInvoice ? formatPrice(selectedInvoice.total_amount) :
                                '' }}
                            </p>
                        </div>
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="payment_proof">Comprovativo de Pagamento *</Label>
                        <Input id="payment_proof" type="file" accept=".pdf,.jpg,.jpeg,.png"
                            @change="onPaymentProofChange" />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showPaymentModal = false">
                        Cancelar
                    </Button>
                    <Button @click="sendPaymentNotification" :disabled="!paymentProofFile">
                        Enviar Email
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>