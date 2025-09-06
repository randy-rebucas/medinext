export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg">
                <span className="text-white font-bold text-sm">M</span>
            </div>
            <div className="ml-3 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-bold text-slate-900 dark:text-white">Medinext</span>
                <span className="truncate text-xs text-slate-600 dark:text-slate-400">EMR System</span>
            </div>
        </>
    );
}
