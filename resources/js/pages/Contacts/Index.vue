<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
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

interface Contact {
    id: number
    entity_id: number
    entity?: { id: number; name: string }
    first_name: string
    last_name: string
    contact_role_id?: number
    contact_role?: { id: number; name: string }
    phone?: string
    mobile?: string
    email?: string
    gdpr_consent: boolean
    notes?: string
    status: string
}

interface Entity {
    id: number
    name: string
}

interface ContactRole {
    id: number
    name: string
}

const props = defineProps<{
    contacts: Contact[]
    entities: Entity[]
    contactRoles: ContactRole[]
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
    entity_id: null as number | null,
    first_name: '',
    last_name: '',
    contact_role_id: null as number | null,
    phone: '',
    mobile: '',
    email: '',
    gdpr_consent: false,
    notes: '',
    status: 'active',
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
    if (isEditing.value && editingId.value !== null) {
        form.put(`/contacts/${editingId.value}`, {
            onSuccess: clearForm,
        })
    } else {
        form.post('/contacts', {
            onSuccess: clearForm,
        })
    }
}

function edit(contact: Contact) {
    isEditing.value = true
    editingId.value = contact.id

    form.entity_id = contact.entity_id
    form.first_name = contact.first_name
    form.last_name = contact.last_name
    form.contact_role_id = contact.contact_role_id ?? null
    form.phone = contact.phone ?? ''
    form.mobile = contact.mobile ?? ''
    form.email = contact.email ?? ''
    form.gdpr_consent = contact.gdpr_consent
    form.notes = contact.notes ?? ''
    form.status = contact.status

    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function destroy(contact: Contact) {
    if (confirm('Tem certeza que deseja eliminar este contacto?')) {
        router.delete(`/contacts/${contact.id}`, {
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
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Contactos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>{{ isEditing ? 'Editar' : 'Novo' }} Contacto</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-6" @submit.prevent="submit">

                            <div class="space-y-2">
                                <Label for="entity_id">Entidade *</Label>
                                <Select :model-value="form.entity_id?.toString()"
                                    @update:model-value="value => form.entity_id = value ? Number(value) : null">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione a entidade..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="entity in entities" :key="entity.id"
                                            :value="entity.id.toString()">
                                            {{ entity.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.entity_id" class="text-sm text-destructive">
                                    {{ form.errors.entity_id }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="first_name">Nome *</Label>
                                    <Input id="first_name" v-model="form.first_name" />
                                    <p v-if="form.errors.first_name" class="text-sm text-destructive">
                                        {{ form.errors.first_name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="last_name">Apelido *</Label>
                                    <Input id="last_name" v-model="form.last_name" />
                                    <p v-if="form.errors.last_name" class="text-sm text-destructive">
                                        {{ form.errors.last_name }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="contact_role_id">Função</Label>
                                <Select :model-value="form.contact_role_id?.toString()"
                                    @update:model-value="value => form.contact_role_id = value ? Number(value) : null">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione a função..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="role in contactRoles" :key="role.id"
                                            :value="role.id.toString()">
                                            {{ role.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <Label for="phone">Telefone</Label>
                                    <Input id="phone" v-model="form.phone" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="mobile">Telemóvel</Label>
                                    <Input id="mobile" v-model="form.mobile" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Email</Label>
                                    <Input id="email" v-model="form.email" type="email" />
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Checkbox id="gdpr_consent" :checked="form.gdpr_consent"
                                    @update:checked="(value: boolean) => form.gdpr_consent = value" />
                                <Label for="gdpr_consent">Consentimento RGPD</Label>
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
                        <CardTitle>Lista de Contactos</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Apelido</TableHead>
                                    <TableHead>Função</TableHead>
                                    <TableHead>Entidade</TableHead>
                                    <TableHead>Telefone</TableHead>
                                    <TableHead>Telemóvel</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="contact in contacts" :key="contact.id">
                                    <TableCell class="font-medium">{{ contact.first_name }}</TableCell>
                                    <TableCell>{{ contact.last_name }}</TableCell>
                                    <TableCell>{{ contact.contact_role?.name ?? '-' }}</TableCell>
                                    <TableCell>{{ contact.entity?.name ?? '-' }}</TableCell>
                                    <TableCell>{{ contact.phone }}</TableCell>
                                    <TableCell>{{ contact.mobile }}</TableCell>
                                    <TableCell>{{ contact.email }}</TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="edit(contact)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(contact)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="contacts.length === 0">
                                    <TableCell colspan="8" class="text-center text-muted-foreground">
                                        Nenhum contacto encontrado.
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