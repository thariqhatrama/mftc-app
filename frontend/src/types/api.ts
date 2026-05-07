export interface PaginatedMeta {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

export interface ApplicationSummary {
  id: string
  scope: string | null
  level: string | null
  status: string
  display_status: string
  version: number
  submitted_at: string | null
  paid_at: string | null
  certified_at: string | null
  created_at: string
  updated_at: string
}

export interface BusinessProfile {
  id: string
  user_id: string
  company_name: string
  nib: string
  address: string
  contact_person: string
  contact_phone: string
  legal_document_url?: string | null
  completed: boolean
  created_at?: string
  updated_at?: string
}
