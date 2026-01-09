<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'

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

interface Entity {
  id: number
  type: string | string[]
  tax_number: string
  name: string
  address?: string
  postal_code?: string
  city?: string
  country_id?: number
  phone?: string
  mobile?: string
  website?: string
  email: string
  gdpr_consent: boolean
  notes?: string
  status: string
}

interface Country {
  id: number
  name: string
}

const props = defineProps<{
  entities: Entity[]
  countries: Country[]
  filterType?: 'client' | 'supplier'
}>()

/* -------------------------------------------------------------------------- */
/* STATE */
/* -------------------------------------------------------------------------- */

const isEditing = ref(false)
const editingId = ref<number | null>(null)

/* -------------------------------------------------------------------------- */
/* FORM (Inertia) */
/* -------------------------------------------------------------------------- */

const form = useForm({
  type: props.filterType ? [props.filterType] : ([] as string[]),
  tax_number: '',
  name: '',
  address: '',
  postal_code: '',
  city: '',
  country_id: null as number | null,
  phone: '',
  mobile: '',
  website: '',
  email: '',
  gdpr_consent: false,
  notes: '',
  status: 'active',
})

/* -------------------------------------------------------------------------- */
/* ROUTE HELPER */
/* -------------------------------------------------------------------------- */

const route = (name: string, ...params: unknown[]) => {
  const page = usePage()
  const routes = (page.props as any).ziggy?.routes || {}
  const routePattern = routes[name]

  if (!routePattern) return '#'

  let url = routePattern.uri
  params.forEach(param => {
    url = url.replace(/\{[^}]+\}/, String(param))
  })

  return url
}

/* -------------------------------------------------------------------------- */
/* COMPUTED */
/* -------------------------------------------------------------------------- */

const pageTitle = computed(() => {
  if (props.filterType === 'client') return 'Clientes'
  if (props.filterType === 'supplier') return 'Fornecedores'
  return 'Entidades'
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
  if (isEditing.value && editingId.value !== null) {
    form.put(`/entities/${editingId.value}`, {
      onSuccess: clearForm,
    })
  } else {
    form.post('/entities', {
      onSuccess: clearForm,
    })
  }
}

function edit(entity: Entity) {
  isEditing.value = true
  editingId.value = entity.id

  form.type = Array.isArray(entity.type) ? entity.type : [entity.type]
  form.tax_number = entity.tax_number
  form.name = entity.name
  form.address = entity.address ?? ''
  form.postal_code = entity.postal_code ?? ''
  form.city = entity.city ?? ''
  form.country_id = entity.country_id ?? null
  form.phone = entity.phone ?? ''
  form.mobile = entity.mobile ?? ''
  form.website = entity.website ?? ''
  form.email = entity.email
  form.gdpr_consent = entity.gdpr_consent
  form.notes = entity.notes ?? ''
  form.status = entity.status

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function destroy(entity: Entity) {
  if (confirm('Tem certeza que deseja eliminar esta entidade?')) {
    router.delete(`/entities/${entity.id}`, {
      preserveScroll: true,
    })
  }
}

function clearForm() {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.type = props.filterType ? [props.filterType] : []
}
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ pageTitle }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <Card>
          <CardHeader>
            <CardTitle>{{ isEditing ? 'Editar' : 'Nova' }} Entidade</CardTitle>
          </CardHeader>

          <CardContent>
            <form @submit.prevent="submit" class="space-y-6">

              <div class="space-y-2">
                <Label>Tipo *</Label>

                <div class="flex gap-6">
                  <div class="flex items-center gap-2">
                    <input type="radio" id="type-client" value="client" v-model="form.type[0]" class="h-4 w-4" />
                    <Label for="type-client" class="cursor-pointer">Cliente</Label>
                  </div>

                  <div class="flex items-center gap-2">
                    <input type="radio" id="type-supplier" value="supplier" v-model="form.type[0]" class="h-4 w-4" />
                    <Label for="type-supplier" class="cursor-pointer">Fornecedor</Label>
                  </div>
                </div>

                <p v-if="form.errors.type" class="text-sm text-destructive">
                  {{ form.errors.type }}
                </p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="tax_number">NIF *</Label>
                  <Input id="tax_number" v-model="form.tax_number" />
                  <p v-if="form.errors.tax_number" class="text-sm text-destructive">
                    {{ form.errors.tax_number }}
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
                <Label for="address">Morada</Label>
                <Input id="address" v-model="form.address" />
              </div>

              <div class="grid grid-cols-3 gap-4">
                <div class="space-y-2">
                  <Label for="postal_code">Código Postal</Label>
                  <Input id="postal_code" v-model="form.postal_code" placeholder="0000-000" />
                </div>

                <div class="space-y-2">
                  <Label for="city">Localidade</Label>
                  <Input id="city" v-model="form.city" />
                </div>

                <div class="space-y-2">
                  <Label for="country_id">País</Label>
                  <Select :model-value="form.country_id?.toString()"
                    @update:model-value="value => form.country_id = value ? Number(value) : null">
                    <SelectTrigger>
                      <SelectValue placeholder="Selecione..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="country in countries" :key="country.id" :value="country.id.toString()">
                        {{ country.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="phone">Telefone</Label>
                  <Input id="phone" v-model="form.phone" />
                </div>

                <div class="space-y-2">
                  <Label for="mobile">Telemóvel</Label>
                  <Input id="mobile" v-model="form.mobile" />
                </div>

                <div class="space-y-2">
                  <Label for="website">Website</Label>
                  <Input id="website" v-model="form.website" type="url" />
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
            <CardTitle>Lista de {{ pageTitle }}</CardTitle>
          </CardHeader>

          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>NIF</TableHead>
                  <TableHead>Nome</TableHead>
                  <TableHead>Telefone</TableHead>
                  <TableHead>Telemóvel</TableHead>
                  <TableHead>Website</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead class="text-right">Ações</TableHead>
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-for="entity in entities" :key="entity.id">
                  <TableCell>{{ entity.tax_number }}</TableCell>
                  <TableCell class="font-medium">{{ entity.name }}</TableCell>
                  <TableCell>{{ entity.phone }}</TableCell>
                  <TableCell>{{ entity.mobile }}</TableCell>
                  <TableCell>{{ entity.website }}</TableCell>
                  <TableCell>{{ entity.email }}</TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-2">
                      <Button size="sm" variant="outline" @click="edit(entity)">Editar</Button>
                      <Button size="sm" variant="destructive" @click="destroy(entity)">Apagar</Button>
                    </div>
                  </TableCell>
                </TableRow>

                <TableRow v-if="entities.length === 0">
                  <TableCell colspan="7" class="text-center text-muted-foreground">
                    Nenhuma entidade encontrada.
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
