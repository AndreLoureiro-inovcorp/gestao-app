<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

/* -------------------------------------------------------------------------- */
/* TYPES */
/* -------------------------------------------------------------------------- */

interface CompanySetting {
  id?: number
  logo?: string
  name: string
  address?: string
  postal_code?: string
  city?: string
  tax_number: string
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { settings } = defineProps<{
  settings: CompanySetting
}>()

/* -------------------------------------------------------------------------- */
/* FORM */
/* -------------------------------------------------------------------------- */

const form = useForm({
  logo: settings.logo ?? '',
  name: settings.name ?? '',
  address: settings.address ?? '',
  postal_code: settings.postal_code ?? '',
  city: settings.city ?? '',
  tax_number: settings.tax_number ?? '',
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
  form.post('/settings/company')
}
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Configurações - Empresa
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
        
        <Card>
          <CardHeader>
            <CardTitle>Dados da Empresa</CardTitle>
          </CardHeader>

          <CardContent>
            <form class="space-y-6" @submit.prevent="submit">
              
              <!-- Logo -->
              <div class="space-y-2">
                <Label for="logo">Logotipo (URL)</Label>
                <Input id="logo" v-model="form.logo" type="url" placeholder="https://..." />
                <p class="text-xs text-muted-foreground">
                  Por agora, insira o URL do logotipo. Upload será implementado depois.
                </p>
              </div>

              <!-- Nome e NIF -->
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="name">Nome da Empresa *</Label>
                  <Input id="name" v-model="form.name" />
                  <p v-if="form.errors.name" class="text-sm text-destructive">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label for="tax_number">Número Contribuinte *</Label>
                  <Input id="tax_number" v-model="form.tax_number" />
                  <p v-if="form.errors.tax_number" class="text-sm text-destructive">
                    {{ form.errors.tax_number }}
                  </p>
                </div>
              </div>

              <!-- Morada -->
              <div class="space-y-2">
                <Label for="address">Morada</Label>
                <Textarea id="address" v-model="form.address" />
              </div>

              <!-- Código Postal e Localidade -->
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="postal_code">Código Postal</Label>
                  <Input id="postal_code" v-model="form.postal_code" placeholder="0000-000" />
                </div>

                <div class="space-y-2">
                  <Label for="city">Localidade</Label>
                  <Input id="city" v-model="form.city" />
                </div>
              </div>

              <!-- Botão -->
              <div>
                <Button type="submit" :disabled="form.processing">
                  Guardar Configurações
                </Button>
              </div>

            </form>
          </CardContent>
        </Card>

      </div>
    </div>
  </AppLayout>
</template>