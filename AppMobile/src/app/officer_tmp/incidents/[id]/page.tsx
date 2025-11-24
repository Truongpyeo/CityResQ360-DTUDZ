export const metadata = {
  title: "Incident Details",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Incident Details</h1>
      <p>Full summary for a specific incident. Currently viewing record {params.id}.</p>
    </main>
  );
}
