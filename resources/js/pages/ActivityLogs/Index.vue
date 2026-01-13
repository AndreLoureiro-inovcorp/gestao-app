<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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

interface Activity {
    id: number
    log_name: string | null
    description: string
    subject_type: string | null
    subject_id: number | null
    causer_type: string | null
    causer_id: number | null
    event: string | null
    properties: any
    created_at: string
    causer?: {
        id: number
        name: string
    }
}

interface LogsData {
    data: Activity[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { logs } = defineProps<{
    logs: LogsData
}>()

/* -------------------------------------------------------------------------- */
/* HELPERS */
/* -------------------------------------------------------------------------- */

function formatDateTime(dateString: string): string {
    const date = new Date(dateString)
    return date.toLocaleString('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function getEventLabel(event: string | null): string {
    const labels: Record<string, string> = {
        created: 'Criou',
        updated: 'Editou',
        deleted: 'Apagou',
    }
    return event ? labels[event] || event : '-'
}

function getModelName(subjectType: string | null): string {
    if (!subjectType) return '-'

    const parts = subjectType.split('\\')
    const modelName = parts[parts.length - 1]

    const translations: Record<string, string> = {
        Proposal: 'Proposta',
        ClientOrder: 'Encomenda Cliente',
        SupplierOrder: 'Encomenda Fornecedor',
        Article: 'Artigo',
        Entity: 'Entidade',
        Contact: 'Contacto',
        VatRate: 'Taxa IVA',
        ContactRole: 'Função',
    }

    return translations[modelName] || modelName
}

function getEventClass(event: string | null): string {
    const classes: Record<string, string> = {
        created: 'bg-green-100 text-green-800',
        updated: 'bg-blue-100 text-blue-800',
        deleted: 'bg-red-100 text-red-800',
    }
    return event ? classes[event] || 'bg-gray-100 text-gray-800' : 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Logs de Atividade
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>Histórico de Atividades</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Data/Hora</TableHead>
                                    <TableHead>Utilizador</TableHead>
                                    <TableHead>Ação</TableHead>
                                    <TableHead>Modelo</TableHead>
                                    <TableHead>Descrição</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="log in logs.data" :key="log.id">
                                    <TableCell class="whitespace-nowrap">
                                        {{ formatDateTime(log.created_at) }}
                                    </TableCell>
                                    <TableCell>
                                        {{ log.causer?.name || 'Sistema' }}
                                    </TableCell>
                                    <TableCell>
                                        <span :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            getEventClass(log.event)
                                        ]">
                                            {{ getEventLabel(log.event) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        {{ getModelName(log.subject_type) }}
                                    </TableCell>
                                    <TableCell class="max-w-xs truncate">
                                        {{ log.description || '-' }}
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="logs.data.length === 0">
                                    <TableCell colspan="5" class="text-center text-muted-foreground">
                                        Nenhuma atividade registada.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <div v-if="logs.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Página {{ logs.current_page }} de {{ logs.last_page }}
                                ({{ logs.total }} registos)
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>