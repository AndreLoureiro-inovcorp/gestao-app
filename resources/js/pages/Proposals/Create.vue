<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
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

interface Client {
    id: number
    name: string
}

interface VatRate {
    id: number
    name: string
    rate: number
}

interface Article {
    id: number
    reference: string
    name: string
    price: number
    vat_rate?: VatRate
}

interface Supplier {
    id: number
    name: string
}

interface ArticleLine {
    article_id: number | null
    quantity: number
    unit_price: number
    supplier_id: number | null
    cost_price: number | null
    vat_rate: number
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { clients, articles, suppliers } = defineProps<{
    clients: Client[]
    articles: Article[]
    suppliers: Supplier[]
}>()

/* -------------------------------------------------------------------------- */
/* STATE */
/* -------------------------------------------------------------------------- */

const articleLines = ref<ArticleLine[]>([
    {
        article_id: null,
        quantity: 1,
        unit_price: 0,
        supplier_id: null,
        cost_price: null,
        vat_rate: 0,
    }
])

/* -------------------------------------------------------------------------- */
/* FORM */
/* -------------------------------------------------------------------------- */

const form = useForm({
    client_id: null as number | null,
    validity_date: '',
    notes: '',
    status: 'draft',
    articles: [] as any[],
})

/* -------------------------------------------------------------------------- */
/* COMPUTED */
/* -------------------------------------------------------------------------- */

const subtotalWithoutVat = computed(() => {
    return articleLines.value.reduce((total, line) => {
        return total + (line.quantity * line.unit_price)
    }, 0)
})

const totalVat = computed(() => {
    return articleLines.value.reduce((total, line) => {
        const subtotal = line.quantity * line.unit_price
        const vat = subtotal * (line.vat_rate / 100)
        return total + vat
    }, 0)
})

const totalAmount = computed(() => {
    return subtotalWithoutVat.value + totalVat.value
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function addLine() {
    articleLines.value.push({
        article_id: null,
        quantity: 1,
        unit_price: 0,
        supplier_id: null,
        cost_price: null,
        vat_rate: 0,
    })
}

function removeLine(index: number) {
    if (articleLines.value.length > 1) {
        articleLines.value.splice(index, 1)
    }
}

function onArticleChange(index: number, articleId: number) {
    const article = articles.find(a => a.id === articleId)
    if (article) {
        articleLines.value[index].article_id = articleId
        articleLines.value[index].unit_price = article.price
        articleLines.value[index].vat_rate = article.vat_rate?.rate || 0
    }
}

function getLineSubtotal(line: ArticleLine): number {
    return line.quantity * line.unit_price
}

function submit() {
    const validLines = articleLines.value.filter(line => line.article_id !== null)

    if (validLines.length === 0) {
        alert('Adicione pelo menos um artigo à proposta!')
        return
    }

    form.articles = validLines

    form.post('/proposals', {
        onSuccess: () => {
            router.visit('/proposals')
        },
    })
}

function cancel() {
    router.visit('/proposals')
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2,
    }).format(price)
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nova Proposta
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>Criar Proposta</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-6" @submit.prevent="submit">

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="client_id">Cliente *</Label>
                                    <Select :model-value="form.client_id?.toString()"
                                        @update:model-value="value => form.client_id = value ? Number(value) : null">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione o cliente..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="client in clients" :key="client.id"
                                                :value="client.id.toString()">
                                                {{ client.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.client_id" class="text-sm text-destructive">
                                        {{ form.errors.client_id }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="validity_date">Data Validade</Label>
                                    <Input id="validity_date" v-model="form.validity_date" type="date" />
                                    <p class="text-xs text-muted-foreground">
                                        Por defeito: 30 dias a contar de hoje
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <Label class="text-lg">Artigos da Proposta</Label>
                                    <Button type="button" variant="outline" size="sm" @click="addLine">
                                        + Adicionar Artigo
                                    </Button>
                                </div>

                                <div class="grid grid-cols-12 gap-2 rounded-md bg-gray-50 p-2 text-sm font-medium">
                                    <div class="col-span-3">Artigo</div>
                                    <div class="col-span-2">Quantidade</div>
                                    <div class="col-span-2">Preço Unit.</div>
                                    <div class="col-span-2">Fornecedor</div>
                                    <div class="col-span-2">Preço Custo</div>
                                    <div class="col-span-1 text-right">Ações</div>
                                </div>

                                <div v-for="(line, index) in articleLines" :key="index"
                                    class="grid grid-cols-12 gap-2 rounded-md border p-2">
                                    <div class="col-span-3">
                                        <Select :model-value="line.article_id?.toString()"
                                            @update:model-value="value => onArticleChange(index, Number(value))">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="article in articles" :key="article.id"
                                                    :value="article.id.toString()">
                                                    {{ article.reference }} - {{ article.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="col-span-2">
                                        <Input v-model.number="line.quantity" type="number" step="1" min="1" />
                                    </div>

                                    <div class="col-span-2">
                                        <Input v-model.number="line.unit_price" type="number" step="0.01" min="0" />
                                    </div>

                                    <div class="col-span-2">
                                        <Select :model-value="line.supplier_id?.toString()"
                                            @update:model-value="value => line.supplier_id = value ? Number(value) : null">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Opcional..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="supplier in suppliers" :key="supplier.id"
                                                    :value="supplier.id.toString()">
                                                    {{ supplier.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="col-span-2">
                                        <Input :model-value="line.cost_price ?? ''"
                                            @update:model-value="value => line.cost_price = value ? Number(value) : null"
                                            type="number" step="0.01" min="0" placeholder="Custo (opcional)" />
                                    </div>

                                    <div class="col-span-1 flex items-center justify-end">
                                        <Button type="button" variant="ghost" size="sm" @click="removeLine(index)"
                                            :disabled="articleLines.length === 1">
                                            ✕
                                        </Button>
                                    </div>

                                    <div class="col-span-12 text-right text-sm space-y-1">
                                        <div v-if="line.vat_rate > 0" class="text-gray-500 text-xs">
                                            IVA {{ line.vat_rate }}%
                                        </div>
                                        <div v-if="line.cost_price" class="text-xs text-gray-500">
                                            (Custo: {{ formatPrice(line.quantity * line.cost_price) }}
                                            | Margem: {{ formatPrice(getLineSubtotal(line) - (line.quantity *
                                                line.cost_price))
                                            }})
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-md bg-blue-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-medium">TOTAL:</span>
                                        <span class="text-2xl font-bold text-blue-600">
                                            {{ formatPrice(totalAmount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="notes">Observações</Label>
                                <Textarea id="notes" v-model="form.notes" rows="3" />
                            </div>

                            <div class="space-y-2">
                                <Label>Estado *</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger />
                                    <SelectContent>
                                        <SelectItem value="draft">Rascunho</SelectItem>
                                        <SelectItem value="closed">Fechado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex gap-2">
                                <Button type="submit" :disabled="form.processing">
                                    Guardar Proposta
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