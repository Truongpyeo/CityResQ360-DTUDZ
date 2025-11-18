export const metadata = {
  title: "Report Details",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Report Details</h1>
      <p>Detailed information for a specific citizen report. Currently viewing report {params.id}.</p>
    </main>
  );
}
