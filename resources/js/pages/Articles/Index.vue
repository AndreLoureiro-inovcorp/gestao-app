<script setup lang="ts">
import { ref } from 'vue'
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

interface Article {
    id: number
    reference: string
    name: string
    description?: string
    price: number
    vat_rate_id?: number
    vat_rate?: { id: number; name: string; rate: number }
    photo?: string
    notes?: string
    status: string
}

interface VatRate {
    id: number
    name: string
    rate: number
}

const { articles, vatRates } = defineProps<{
    articles: Article[]
    vatRates: VatRate[]
}>()

/* -------------------------------------------------------------------------- */
/* STATE */
/* -------------------------------------------------------------------------- */

const isEditing = ref(false)
const editingId = ref<number | null>(null)

/* -------------------------------------------------------------------------- */
/* FORM */
/* -------------------------------------------------------------------------- */

const form = useForm({
    reference: '',
    name: '',
    description: '',
    price: '' as string | number,
    vat_rate_id: null as number | null,
    photo: '',
    notes: '',
    status: 'active',
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
    if (isEditing.value && editingId.value !== null) {
        form.put(`/articles/${editingId.value}`, {
            onSuccess: clearForm,
        })
    } else {
        form.post('/articles', {
            onSuccess: clearForm,
        })
    }
}

function edit(article: Article) {
    isEditing.value = true
    editingId.value = article.id

    form.reference = article.reference
    form.name = article.name
    form.description = article.description ?? ''
    form.price = article.price
    form.vat_rate_id = article.vat_rate_id ?? null
    form.photo = article.photo ?? ''
    form.notes = article.notes ?? ''
    form.status = article.status

    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function destroy(article: Article) {
    if (confirm('Tem certeza que deseja eliminar este artigo?')) {
        router.delete(`/articles/${article.id}`, {
            preserveScroll: true,
        })
    }
}

function clearForm() {
    isEditing.value = false
    editingId.value = null
    form.reset()
    form.status = 'active'
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(price)
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Artigos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>{{ isEditing ? 'Editar' : 'Novo' }} Artigo</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-6" @submit.prevent="submit">

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="reference">Referência *</Label>
                                    <Input id="reference" v-model="form.reference" />
                                    <p v-if="form.errors.reference" class="text-sm text-destructive">
                                        {{ form.errors.reference }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="name">Nome *</Label>
                                    <Input id="name" v-model="form.name" />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Descrição</Label>
                                <Textarea id="description" v-model="form.description" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="price">Preço (€) *</Label>
                                    <Input id="price" v-model="form.price" type="number" step="0.01" min="0" />
                                    <p v-if="form.errors.price" class="text-sm text-destructive">
                                        {{ form.errors.price }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="vat_rate_id">IVA</Label>
                                    <Select :model-value="form.vat_rate_id?.toString()"
                                        @update:model-value="value => form.vat_rate_id = value ? Number(value) : null">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecione o IVA..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="vat in vatRates" :key="vat.id"
                                                :value="vat.id.toString()">
                                                {{ vat.name }} ({{ vat.rate }}%)
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="photo">Foto (URL)</Label>
                                <Input id="photo" v-model="form.photo" type="url" placeholder="https://..." />
                                <p class="text-xs text-muted-foreground">
                                    Por agora, insira o URL de uma imagem. Upload será implementado depois.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="notes">Observações</Label>
                                <Textarea id="notes" v-model="form.notes" />
                            </div>

                            <div class="space-y-2">
                                <Label>Estado *</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger />
                                    <SelectContent>
                                        <SelectItem value="active">Ativo</SelectItem>
                                        <SelectItem value="inactive">Inativo</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex gap-2">
                                <Button type="submit" :disabled="form.processing">
                                    {{ isEditing ? 'Atualizar' : 'Guardar' }}
                                </Button>
                                <Button type="button" variant="outline" @click="clearForm">
                                    Limpar
                                </Button>
                            </div>

                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Artigos</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Referência</TableHead>
                                    <TableHead>Foto</TableHead>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Descrição</TableHead>
                                    <TableHead>Preço</TableHead>
                                    <TableHead>IVA</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="article in articles" :key="article.id">
                                    <TableCell class="font-medium">{{ article.reference }}</TableCell>
                                    <TableCell>
                                        <img v-if="article.photo" :src="article.photo" :alt="article.name"
                                            class="h-10 w-10 rounded object-cover" />
                                        <div v-else
                                            class="flex h-10 w-10 items-center justify-center rounded bg-gray-200 text-xs text-gray-500">
                                            Sem foto
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ article.name }}</TableCell>
                                    <TableCell class="max-w-xs truncate">{{ article.description }}</TableCell>
                                    <TableCell>{{ formatPrice(article.price) }}</TableCell>
                                    <TableCell>
                                        {{ article.vat_rate ? `${article.vat_rate.name} (${article.vat_rate.rate}%)` :
                                        '-' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="edit(article)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(article)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="articles.length === 0">
                                    <TableCell colspan="7" class="text-center text-muted-foreground">
                                        Nenhum artigo encontrado.
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