<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

/* -------------------------------------------------------------------------- */
/* TYPES */
/* -------------------------------------------------------------------------- */

interface Supplier {
    id: number
    name: string
}

interface SupplierOrder {
    id: number
    number: string
    total_amount: number
    supplier: {
        id: number
        name: string
    }
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { suppliers, supplierOrders } = defineProps<{
    suppliers: Supplier[]
    supplierOrders: SupplierOrder[]
}>()

/* -------------------------------------------------------------------------- */
/* STATE */
/* -------------------------------------------------------------------------- */

const documentFile = ref<File | null>(null)

/* -------------------------------------------------------------------------- */
/* FORM */
/* -------------------------------------------------------------------------- */

const form = useForm({
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: '',
    supplier_id: null as number | null,
    supplier_order_id: null as number | null,
    total_amount: 0,
    document: null as File | null,
    status: 'pending',
})

/* -------------------------------------------------------------------------- */
/* WATCH */
/* -------------------------------------------------------------------------- */

// Auto-preencher quando seleciona encomenda
watch(() => form.supplier_order_id, (orderId) => {
    if (orderId) {
        const order = supplierOrders.find(o => o.id === orderId)
        if (order) {
            form.supplier_id = order.supplier.id
            form.total_amount = order.total_amount
        }
    }
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement
    if (target.files && target.files[0]) {
        documentFile.value = target.files[0]
        form.document = target.files[0]
    }
}

function submit() {
    form.post('/supplier-invoices', {
        forceFormData: true,
        onSuccess: () => {
            router.visit('/supplier-invoices')
        },
    })
}

function cancel() {
    router.visit('/supplier-invoices')
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(price)
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nova Fatura Fornecedor
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>Criar Fatura</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-6" @submit.prevent="submit">

                            <div class="space-y-2">
                                <Label for="supplier_order_id">Encomenda Fornecedor (opcional)</Label>
                                <Select :model-value="form.supplier_order_id?.toString()"
                                    @update:model-value="value => form.supplier_order_id = value ? Number(value) : null">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione uma encomenda..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="order in supplierOrders" :key="order.id"
                                            :value="order.id.toString()">
                                            {{ order.number }} - {{ order.supplier.name }} ({{
                                                formatPrice(order.total_amount)
                                            }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p class="text-xs text-muted-foreground">
                                    Selecione para preencher automaticamente fornecedor e valor
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="invoice_date">Data da Fatura *</Label>
                                    <Input id="invoice_date" v-model="form.invoice_date" type="date" required />
                                    <p v-if="form.errors.invoice_date" class="text-sm text-destructive">
                                        {{ form.errors.invoice_date }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="due_date">Data de Emissão *</Label>
                                    <Input id="due_date" v-model="form.due_date" type="date" required />
                                    <p v-if="form.errors.due_date" class="text-sm text-destructive">
                                        {{ form.errors.due_date }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="supplier_id">Fornecedor *</Label>
                                <Select :model-value="form.supplier_id?.toString()"
                                    @update:model-value="value => form.supplier_id = value ? Number(value) : null">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione o fornecedor..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="supplier in suppliers" :key="supplier.id"
                                            :value="supplier.id.toString()">
                                            {{ supplier.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.supplier_id" class="text-sm text-destructive">
                                    {{ form.errors.supplier_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="total_amount">Valor Total *</Label>
                                <Input id="total_amount" v-model.number="form.total_amount" type="number" step="0.01"
                                    min="0" required />
                                <p v-if="form.errors.total_amount" class="text-sm text-destructive">
                                    {{ form.errors.total_amount }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="document">Documento (PDF, JPG, PNG)</Label>
                                <Input id="document" type="file" accept=".pdf,.jpg,.jpeg,.png" @change="onFileChange" />
                                <p v-if="form.errors.document" class="text-sm text-destructive">
                                    {{ form.errors.document }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Estado *</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger />
                                    <SelectContent>
                                        <SelectItem value="pending">Pendente de Pagamento</SelectItem>
                                        <SelectItem value="paid">Paga</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-sm text-destructive">
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <div class="flex gap-2">
                                <Button type="submit" :disabled="form.processing">
                                    Guardar Fatura
                                </Button>
                                <Button type="button" variant="outline" @click="cancel">
                                    Cancelar
                                </Button>
                            </div>

                        </form>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>