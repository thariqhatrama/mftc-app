interface WizardStep {
  number: 1 | 2 | 3
  label: string
}

const steps: WizardStep[] = [
  { number: 1, label: 'Ruang Lingkup' },
  { number: 2, label: 'Lokasi Multisitus' },
  { number: 3, label: 'Pra-Assessment' },
]

export function WizardStepper({ currentStep }: { currentStep: 1 | 2 | 3 }) {
  return (
    <div className="relative flex justify-between items-center mb-12 px-4 md:px-10">
      <div className="absolute top-[20px] left-10 right-10 h-[2px] bg-gray-200 -z-10"></div>
      <div
        className="absolute top-[20px] left-10 h-[2px] bg-primary -z-10 transition-all"
        style={{ width: currentStep === 1 ? '0%' : currentStep === 2 ? '50%' : 'calc(100% - 5rem)' }}
      ></div>
      {steps.map((step) => {
        const done = step.number < currentStep
        const active = step.number === currentStep

        return (
          <div key={step.number} className="flex flex-col items-center gap-3">
            <div
              className={
                done
                  ? 'w-10 h-10 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold ring-4 ring-white border-2 border-emerald-100'
                  : active
                    ? 'w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold ring-4 ring-white border-2 border-primary'
                    : 'w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold ring-4 ring-white border-2 border-gray-200'
              }
            >
              {done ? <span className="material-symbols-outlined text-sm">check</span> : step.number}
            </div>
            <span
              className={
                done
                  ? 'text-[11px] font-bold text-emerald-700 uppercase tracking-wider'
                  : active
                    ? 'text-[11px] font-bold text-primary uppercase tracking-wider'
                    : 'text-[11px] font-bold text-gray-400 uppercase tracking-wider'
              }
            >
              {step.label}
            </span>
          </div>
        )
      })}
    </div>
  )
}

export default WizardStepper
